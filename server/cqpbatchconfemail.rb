#!/usr/local/ruby/bin/ruby
# -*- encoding: utf-8 -*-
# CQP Batch Confirmation email program
# Tom Epperly NS6T
# ns6t@arrl.net
#
# This looks through the database of received logs and sends a confirmation
# log for the most recently received log.
#
# This also ensures that Google's outgoing email limit of 500 emails in a
# 24 hour period is not exceeded
#

require 'fileutils'
require_relative 'database'
require_relative 'email'
require_relative 'oauthaccesstok'

LOCKDIR="/var/lock/cqpbatchconfemail"
LOCKFILE=LOCKDIR+"/running.lck"
MAXEMAILSPERDAY=490
FileUtils.mkdir_p(LOCKDIR) unless Dir.exist?(LOCKDIR)
FileUtils.chmod(0700, [ LOCKDIR ])
FileUtils.touch([LOCKFILE])

# ensure only one running instance of this program at a time
exit unless File.new(LOCKFILE).tap { |f| f.autoclose = false}.flock(File::LOCK_NB | File::LOCK_EX)

def numEmailsSent(db)
  result = db.query("select count(*) from CQPLog where confirmstatus in ('sent', 'fail') and confirmtime is not null and confirmtime >= date_sub(now(), interval 1 day);",
                    :as => :array)
  result.each { |row|
    return row[0]
  }
  0
end

def logsToConfirm(db, numSent)
  result = [ ]
  if numSent < MAXEMAILSPERDAY
    res = db.query("select l1.id, l1.callsign, l1.uploadinstant from CQPLog as l1 where l1.confirmstatus = 'pending' and l1.completed and l1.uploadinstant = (select max(l2.uploadinstant) from CQPLog as l2 where l2.confirmstatus='pending' and l2.completed and l2.callsign = l1.callsign) group by l1.callsign order by l1.uploadinstant asc, l1.callsign asc limit #{[0, MAXEMAILSPERDAY-numSent].max};", :as => :array)
    res.each { |row|
      result << [ row[0].to_i, row[1].to_s, row[2].to_f ]
    }
  end
  result
end

def markConfirmed(db, id, callsign, uploadinstant, status)
  db.query("update CQPLog set confirmstatus='#{Mysql2::Client::escape(status)}', confirmtime=NOW() where id = #{id} and confirmstatus='pending' limit 1;")
  db.query("update CQPLog set confirmstatus='skipped' where callsign='#{Mysql2::Client::escape(callsign)}' and confirmstatus='pending' and completed and uploadinstant <= #{uploadinstant};")
end

db = LogDatabase.new
dbcon = db.connect
numSent = numEmailsSent(dbcon)     # num sent in the last 24 hours
if numSent < MAXEMAILSPERDAY
  logs = logsToConfirm(dbcon, numSent)
  logger = DBLogger.new(dbcon)
  if not logs.empty?
    accessTok = OAuthAccessTok.new(logger: logger, conn: dbcon)
    logs.each { |log|
      begin
        confEmail(db, db.getEntry(log[0]), accessTok)
        markConfirmed(dbcon, log[0], log[1], log[2], 'sent')
      rescue => e
        $stderr.write("Exception during email: #{e.class.to_s} #{e.message}\n")
        logger.logException(e)
        markConfirmed(dbcon, log[0], log[1], log[2], 'fail')
      end
    }
  end
end
    
