#!/usr/bin/env ruby
# -*- encoding: utf-8 -*-
# CQP upload database module
# Tom Epperly NS6T
# ns6t@arrl.net
#
#
# create user 'cqpuser'@'localhost' identified by 'cqp234ddx';
# grant all on CQPUploads.* to 'cqpuser'@'localhost' ;
# flush privileges;
#


require 'mysql2'

class DatabaseError < Exception
end


class LogDatabase
  def new
    @connection = nil
  end

  DBTIMEFORMAT="%Y-%m-%d %H:%M:%S.%L"
  MAXIDINT=9007199254740992  # chosen due to Javascript's biggestInt

  def connect
    if not @connection
      @connection = Mysql2::Client.new(:host => "localhost",
                                       :username => "cqpuser",
                                       :password => "cqp234ddx")
      if @connection 
        @connection.query("create database if not exists CQPUploads character set = 'utf8';")
        @connection.query("use CQPUploads;")
        @connection.query("create table if not exists CQPLog (id bigint primary key, callsign varchar(32), originalfile varchar(1024), asciifile varchar(1024), logencoding varchar(32), origdigest char(40), uploadtime datetime, emailaddr varchar(256), phonenum varchar(32), comments varchar(4096), county tinyint(1) unsigned,  youth tinyint(1) unsigned, mobile tinyint(1) unsigned, female tinyint(1) unsigned, school tinyint(1) unsigned, newcontester tinyint(1) unsigned, completed tinyint(1), index callindex (callsign asc));")
      end
    end
    @connection
  end

  def getOne(str)
    result = @connection.query(str, :as => :array)
    if result
      result.each { |row|
        return row[0]
      }
    end
    nil
  end

  def getID
    connect
    if @connection
      id = nil
      tries = 0
      while not id and tries  < 10
        begin
          id = getOne("select cast(rand()*#{MAXIDINT} as signed integer) as id;").to_i
          @connection.query("insert into CQPLog (id) values (#{id});")
        rescue Mysql2::Error    # ID collision with previous entry
          id = nil
          tries = tries + 1
        end
      end
    end
    id
  end

  def addLog(callsign, origfile, asciifile, encoding, timestamp, digest)
    connect
    if @connection
      id = getID
      if id
        @connection.query("update CQPLog set callsign='#{Mysql2::Client::escape(callsign)}', originalfile='#{Mysql2::Client::escape(origfile)}', asciifile='#{Mysql2::Client::escape(asciifile)}', logencoding='#{Mysql2::Client::escape(encoding)}', uploadtime='#{timestamp.strftime(DBTIMEFORMAT)}', origdigest='#{Mysql2::Client::escape(digest)}' where id = #{id.to_i} limit 1;")
        return id
      end
    end
    raise DatabaseError, "Foo"
  end

  def addExtra(id, email, phone, comments, county, youth, mobile, female, school, newcontester)
    connect
    if @connection
      queryStr = "update CQPLog set emailaddr='#{Mysql2::Client::escape(email)}', phonenum='#{Mysql2::Client::escape(phone)}', comments='#{Mysql2::Client::escape(comments)}', county=#{county.to_i}, youth=#{youth.to_i}, mobile= #{mobile.to_i}, female=#{female.to_i}, school=#{school.to_i}, newcontester=#{newcontester.to_i}, completed=1 where id = #{id.to_i} limit 1;"
      $outfile.write(queryStr + "\n");
      @connection.query(queryStr)
      true
    else
      false
    end
  end

  def callsignsRcvd
    result = [ ]
    connect
    if @connection
      res = @connection.query("select distinct callsign from CQPLog where completed order by callsign asc;")
      res.each { |row|
        sign = row["callsign"]
        result << sign unless "UNKNOWN" == sign
      }
    end
    result
  end

end
