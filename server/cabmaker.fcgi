#!/usr/local/ruby/bin/ruby
# -*- encoding: utf-8 -*-
#
# Cabmaker Save server
# ns6t@arrl.net

require 'fcgi'
require 'cgi/cookie'

COOKIE_NAME = "cabmaker_id"
ONE_MONTH = 31 * 24 * 60 * 60
ID_CHARSET = [('a'..'z'), ('A'..'Z'), ('0'..'9')].map { |i| i.to_a }.flatten

def randomID
  (0..16).map { ID_CHARSET[rand(ID_CHARSET.length)] }.join
end

def handleRequest(req)
  # update expiration time
  cookie = req.cookies[COOKIE_NAME]
  if cookie and cookie.kind_of?(Array) and not cookie.kind_of?(CGI::Cookie)
    if cookie.empty?
      cookie= nil
    else
      cookie = cookie[0]
    end
  end

  if not cookie
    cookie = CGI::Cookie::new(COOKIE_NAME, randomID)
  end
  cookie.expires = Time.now + ONE_MONTH

  req.out('cookie' => [ cookie ]) {
'<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CQP Manual Cabrillo Maker</title>
    <link rel="icon" href="/cqp/favicon.ico" sizes="16x16 32x32 40x40 64x64 128x128" type="image/vnd.microsoft.icon">
<style type="text/css">
.mode {
    width: 50px;
}
.date {
    width: 75px;
    font: "monospace";
}
.time {
    width: 60px;
    font: "monospace";
}
.call, .sent_call {
    width: 75px;
    font: "monospace";
}
.serial, .sent_serial {
    width: 45px;
    font: "monospace";
}
.location , .sent_location {
    width: 45px;
    font: "monospace";
}
.cabrillo {
    overflow: scroll;
    width: 95%;
    height: 300px;
    font: "monospace";
}
</style>    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/cqp/js/vendor/jquery.ui.widget.js" type="text/javascript"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js" type="text/javascript"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/additional-methods.min.js" type="text/javascript"></script>
  </head>
  <body>
    <p>
      This form is designed to generate a Cabrillo file for the
      California QSO party.  This form may not work for everyone. It
      requires a relatively recent browser.
    </p>
    <form  id="cqpCabFormID">
      <label for="callsignID">Callsign*:</label>
      <input type="text" name="callsign" value="" id="callsignID"> (call transmitted during contest)<br >
      <label for="nameID">Name:</label>
      <input type="text" name="name" value="" id="nameID"> (your name)<br >
      <label for="opnumID">Number of Operators*:</label>
      <select name="opnum" id="opnumID">
	<option value="SINGLE-OP" selected="selected">Only one</option>
	<option value="MULTI-OP">More than one</option>
	<option value="CHECKLOG">Checklog</option>
      </select><br >
      <label for="transnumID">Number of Transmitters*:</label>
      <select name="transnum" id="transnumID">
	<option value="ONE" selected="selected">Only one</option>
	<option value="UNLIMITED">More than one</option>
      </select> <br>
      <label for="powerID">Power*:</label>
      <select name="power" id="powerID" class="jForm">
	<option value="LOW">Low Power</option>
	<option value="HIGH" selected="selected">High Power</option>
	<option value="QRP">QRP</option>
      </select> <br>
      <label for="assistedID">Assisted:</label>
      <input type="checkbox" name="assisted" value="Yes" id="assistedID" checked>
      <br>
      <label for="clubID">Club:</label>
      <input type="text" name="club" id="clubID"> (to count your score as part of a contesting club) <br>
      <label for="emailID" class="inLabel">Email*:</label>
      <input type="email" name="email" id="emailID" class="jForm"><br>
      <label for="confirmID" class="inLabel">Confirm Email*:</label>
      <input type="email" name="confirm" id="confirmID" class="jForm"><br >
      <label for="tranlocID">Transmitted location:</label>
      <input type="text" name="transloc" id="translocID">
      (abbreviation for your CA County, US state, Canadian Province or DX)
      <br><br>
      <label for="numqID">Number of QSOs*:</label>
      <input type="text" name="numq" id="numqID" value="1"> (number here sets the size of the QSO grid)
      <br>
      <input type="button" name="MakeQSOs" id="MakeQSOsID" value="Make QSO lines" >
      <br>
      <table id="qsoTableID">
	<caption>QSO Entry Grid</caption>
	<tbody>
	  <tr>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>Date</th>
	    <th>Time UTC</th>
	    <th colspan="3">Your station info</th>
	    <th colspan="3">Received station info</th>
	  </tr>
	  <tr>
	    <th>Band</th>
	    <th>Mode</th>
	    <th>(YYYY-MM-DD)</th>
	    <th>(HHMM)</th>
	    <th>Callsign</th>
	    <th>Serial #</th>
	    <th>Location</th>
	    <th>Callsign</th>
	    <th>Serial #</th>
	    <th>Location</th>
	  </tr>
	  <tr id="qsoline1" class="qsoline">
	    <td>
	      <select name="band1" id="band1ID" class="band">
		<option value="440MHz" >440MHz</option>
		<option value="2m" >2m</option>
		<option value="6m" >6m</option>
		<option value="10m" selected="selected">10m</option>
		<option value="15m" >15m</option>
		<option value="20m" >20m</option>
		<option value="40m" >40m</option>
		<option value="80m" >80m</option>
		<option value="160m" >160m</option>
	      </select>
	    </td>
	    <td>
	      <select name="mode1" id="mode1ID" class="mode">
		<option value="PH" selected="selected">PH</option>
		<option value="CW">CW</option>
	      </select>
	    </td>
	    <td>
	      <input name="date1" id="date1" value="2021-10-02" class="date" type="text">
	    </td>
	    <td>
	      <input name="time1" id="time1" value="" class="time" type="text">
	    </td>
	    <td>
	      <input name="sent_call1" id="sent_call1ID" class="sent_call" type="text">
	    </td>
	    <td>
	      <input name="sent_serial1" id="sent_serial1ID" class="sent_serial" type="text">
	    </td>
	    <td>
	      <input name="sent_loc1" id="sent_loc1ID" class="sent_location" type="text">
	    </td>
	    <td>
	      <input name="recvd__call1" id="recvd__call1ID" class="call" type="text">
	    </td>
	    <td>
	      <input name="recvd__serial1" id="recvd__serial1ID" class="serial" type="text">
	    </td>
	    <td>
	      <input name="recvd__loc1" id="recvd__loc1ID" class="location" type="text">
	    </td>
	    
	  </tr>
	</tbody>
      </table>
      <input type="button" value="Generate Cabrillo" id="genCabID">
      <p>Cabrillo File (filled in automatically when you press the "Generate Cabrillo" button)</p>
      <textarea name="cabrillo" class="cabrillo" id="cabrilloID"></textarea>
    </form>
<script  type="text/javascript">
<!--
function updateQSOGrid(grid, num) {
  var numqsos = grid.children().length - 2;
  if (num > 0) {
    if (num < numqsos) { // remove qsos
      while (numqsos > num) {
        $("#qsoline" + numqsos).remove();
	numqsos = numqsos - 1
      }
    }
    else {
       if (num > numqsos) { // add QSOs
          while(numqsos < num) {
            numqsos = numqsos + 1;
	    grid.append("<tr id=\"qsoline" + numqsos + "\" class=\"qsoline\">\
	  <td>\
	      <select name=\"band" + numqsos + "\" id=\"band" +
                 numqsos + "ID\" class=\"band\">\
		<option value=\"440MHz\" >440MHz</option>\
		<option value=\"2m\" >2m</option>\
		<option value=\"6m\" >6m</option>\
		<option value=\"10m\" selected=\"selected\">10m</option>\
		<option value=\"15m\" >15m</option>\
		<option value=\"20m\" >20m</option>\
		<option value=\"40m\" >40m</option>\
		<option value=\"80m\" >80m</option>\
		<option value=\"160m\" >160m</option>\
	      </select>\
	  </td>\
	  <td>\
	    <select name=\"mode" + numqsos + "\" id=\"mode" + numqsos + "ID\" class=\"mode\">\
	      <option value=\"PH\" selected=\"selected\">PH</option>\
	      <option value=\"CW\">CW</option>\
	    </select>\
	  </td>\
	  <td>\
	    <input name=\"date" + numqsos + "\" id=\"date" + numqsos + "\" value=\"2021-10-02\" class=\"date\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"time" + numqsos + "\" id=\"time" + numqsos + "\" value=\"\" class=\"time\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"sent_call" + numqsos + "\" id=\"sent_call" + numqsos + "ID\" class=\"sent_call\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"sent_serial" + numqsos + "\" id=\"sent_serial" + numqsos + "ID\" class=\"sent_serial\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"sent_loc" + numqsos + "\" id=\"sent_loc" + numqsos + "ID\" class=\"sent_location\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"recvd__call" + numqsos + "\" id=\"recvd__call" + numqsos + "ID\" class=\"call\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"recvd__serial" + numqsos + "\" id=\"recvd__serial" + numqsos + "ID\" class=\"serial\" type=\"text\">\
	  </td>\
	  <td>\
	    <input name=\"recvd__loc" + numqsos + "\" id=\"recvd__loc" + numqsos + "ID\" class=\"location\" type=\"text\">\
	  </td>\
	</tr>");
          }
       }
    }
    $(".sent_call").val($("#callsignID").val());
    $(".sent_location").val($("#translocID").val());
    $(".sent_serial").each( function(index, elem) {
       elem.value = index + 1;
    });
  }
}

function qsoButton() {
  updateQSOGrid($("#qsoTableID > tbody"), parseInt($("#numqID").val()));
}

function bandToNum(str) {
  if (str == "440MHz") {
    return "432";
  } else if (str == "2m") {
    return "144";
  } else if (str == "6m") {
    return "50";
  } else if (str == "10m") {
    return "28000";
  } else if (str == "20m") {
    return "14000";
  } else if (str == "40m") { 
    return "7000";
  } else if (str == "80m") {
    return "3500";
  } else if (str == "160m") {
    return "1800";
  }
  return "Unknown";
}

function timeStr(val) {
  if (val.length == 0) {
    return "0000";
  }
  var num = parseInt(val).toString();
  var len = num.length;
  var result = "";
  while (len < 4) {
    result += "0";
    len = len + 1;
  }
  return " " + result + num + " ";
}

function flushLeft(str, len) {
   var i = str.length;
   var result = str;
   while ( i < len) {
     result += " ";
     i += 1;
   }
   return " " + result + " ";
}

function newCabrilloContent() {
  var result = "";
  result += "START-OF-LOG: 3.0\n"
  result += "CALLSIGN: " + $("#callsignID").val() + "\n";
  result += "NAME: " + $("#nameID").val() + "\n";
  result += "CONTEST: CA-QSO-PARTY\n";
  result += "CREATED-BY: cabmaker.html\n";
  if ($("#assistedID").is(":checked")) {
    result += "CATEGORY-ASSISTED: ASSISTED\n";
  }
  else {
    result += "CATEGORY-ASSISTED: NON-ASSISTED\n";
  }
  result += "CATEGORY-POWER: " + $("#powerID").val() +"\n";
  result += "CATEGORY-OPERATOR: " + $("#opnumID").val() + "\n";
  result += "CATEGORY-TRANSMITTER: " + $("#transnumID").val() + "\n";
  result += "CLUB: " + $("#clubID").val() + "\n";
  result += "EMAIL: " + $("#emailID").val() + "\n";
  result += "LOCATION: " + $("#translocID").val() + "\n";
  $(".qsoline").each( function (index, elem) {
    result += "QSO: " + bandToNum(elem.getElementsByClassName("band")[0].value);
    result += " " + elem.getElementsByClassName("mode")[0].value + " ";
    result += " " + elem.getElementsByClassName("date")[0].value + " ";
    result += timeStr(elem.getElementsByClassName("time")[0].value);
    result += flushLeft(elem.getElementsByClassName("sent_call")[0].value,10);
    result += flushLeft(elem.getElementsByClassName("sent_serial")[0].value,4);
    result += flushLeft(elem.getElementsByClassName("sent_location")[0].value,4);
    result += flushLeft(elem.getElementsByClassName("call")[0].value, 10);
    result += flushLeft(elem.getElementsByClassName("serial")[0].value, 4);
    result += flushLeft(elem.getElementsByClassName("location")[0].value, 4);
    result += "\n";
  });
  result += "END-OF-LOG:\n";
  return result;
}

function generateCabrillo() {
  $("#cabrilloID").val(newCabrilloContent()); // clear text area
}

function serializeObj(arg)
{
    var o = {};
    var a = arg.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || "");
        } else {
            o[this.name] = this.value || "";
        }
    });
    return o.toString();
};

var fun = function autosave() { 
    var obj = $("#cqpCabFormID");
    if (console && obj) {
      console.log("JSON: " + serializeObj(obj) + "\n");
    }
    jQuery.ajax({
            url: "http://robot.cqp.org/cqp/server/cabmakersave.fcgi",
            data: serializeObj(obj),
            type: "POST",
            success: function (data) {
                if (data && data == "success") {
                } else {}
            }
        });
}

$(document).ready(function () {
  $("#MakeQSOsID").click(qsoButton);
  $("#genCabID").click(generateCabrillo);
  setInterval(fun, 15000);
});
// -->
</script>
  </body>
</html>
'
  }
end

FCGI.each_cgi { |request|
  begin
    handleRequest(request)
  rescue => e
    $stderr.write(e.message + "\n")
    $stderr.write(e.backtrace.join("\n"))
    $stderr.flush()
    raise
  end
}
