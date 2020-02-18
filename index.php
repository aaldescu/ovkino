<?php
class MyDB extends SQLite3 {
  function __construct() {
	 $this->open('ovkino.db');
  }
}

$db = new MyDB();

if(!$db) {
  echo $db->lastErrorMsg();
} else {
  //continue
}


/**
* Building the Json string with the movie play times for the current week
* kino, movie, hour , day
*/  
$sql =<<<EOF
      SELECT DISTINCT kino,movie,hour,day from PLAYTIME;
EOF;

   $ret = $db->query($sql);
   $events = array();
   while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
      //echo "KINO = ". $row['kino'] . "\n";
      //echo "MOVIE = ".  ."\n";
      //echo "HOUR = ". $row['hour'] ."\n";
      //echo "DAY = ".$row['day'] ."\n\n";
	  $events[]=[ 'title'=> $row['movie'] ,
				  'description' => "Playing in : " . $row['kino'],
				  'start' => $row['day']."T".$row['hour']
				] ;
	  
	  
   }
   $json_events = json_encode($events,JSON_PRETTY_PRINT);
   
   $db->close();


/*
Example Event json_decode
[
{
        title: 'All Day Event',
        start: '2020-02-01'
      },
      {
        title: 'Long Event',
        start: '2020-02-07',
        end: '2020-02-10'
      },
      {
        groupId: '999',
        title: 'Repeating Event',
        start: '2020-02-09T16:00:00'
      },
      {
        groupId: '999',
        title: 'Repeating Event',
        start: '2020-02-16T16:00:00'
      },
      {
        title: 'Conference',
        start: '2020-02-11',
        end: '2020-02-13'
      },
      {
        title: 'Meeting',
        start: '2020-02-12T10:30:00',
        end: '2020-02-12T12:30:00'
      },
      {
        title: 'Lunch',
        start: '2020-02-12T12:00:00'
      },
      {
        title: 'Meeting',
        start: '2020-02-12T14:30:00'
      },
      {
        title: 'Birthday Party',
        start: '2020-02-13T07:00:00'
      },
      {
        title: 'Click for Google',
        url: 'http://google.com/',
        start: '2020-02-28'
      }
]

*/



?>


<html>

<head>
<meta charset='utf-8' />

<style>
html, body {
  margin: 0;
  padding: 0;
  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
  font-size: 14px;
}

#calendar {
  max-width: 900px;
  margin: 40px auto;
}

</style>

<style>

  /*
  i wish this required CSS was better documented :(
  https://github.com/FezVrasta/popper.js/issues/674
  derived from this CSS on this page: https://popper.js.org/tooltip-examples.html
  */

  .popper,
  .tooltip {
    position: absolute;
    z-index: 9999;
    background: #FFC107;
    color: black;
    width: 150px;
    border-radius: 3px;
    box-shadow: 0 0 2px rgba(0,0,0,0.5);
    padding: 10px;
    text-align: center;
  }
  .style5 .tooltip {
    background: #1E252B;
    color: #FFFFFF;
    max-width: 200px;
    width: auto;
    font-size: .8rem;
    padding: .5em 1em;
  }
  .popper .popper__arrow,
  .tooltip .tooltip-arrow {
    width: 0;
    height: 0;
    border-style: solid;
    position: absolute;
    margin: 5px;
  }

  .tooltip .tooltip-arrow,
  .popper .popper__arrow {
    border-color: #FFC107;
  }
  .style5 .tooltip .tooltip-arrow {
    border-color: #1E252B;
  }
  .popper[x-placement^="top"],
  .tooltip[x-placement^="top"] {
    margin-bottom: 5px;
  }
  .popper[x-placement^="top"] .popper__arrow,
  .tooltip[x-placement^="top"] .tooltip-arrow {
    border-width: 5px 5px 0 5px;
    border-left-color: transparent;
    border-right-color: transparent;
    border-bottom-color: transparent;
    bottom: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
  }
  .popper[x-placement^="bottom"],
  .tooltip[x-placement^="bottom"] {
    margin-top: 5px;
  }
  .tooltip[x-placement^="bottom"] .tooltip-arrow,
  .popper[x-placement^="bottom"] .popper__arrow {
    border-width: 0 5px 5px 5px;
    border-left-color: transparent;
    border-right-color: transparent;
    border-top-color: transparent;
    top: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
  }
  .tooltip[x-placement^="right"],
  .popper[x-placement^="right"] {
    margin-left: 5px;
  }
  .popper[x-placement^="right"] .popper__arrow,
  .tooltip[x-placement^="right"] .tooltip-arrow {
    border-width: 5px 5px 5px 0;
    border-left-color: transparent;
    border-top-color: transparent;
    border-bottom-color: transparent;
    left: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
  }
  .popper[x-placement^="left"],
  .tooltip[x-placement^="left"] {
    margin-right: 5px;
  }
  .popper[x-placement^="left"] .popper__arrow,
  .tooltip[x-placement^="left"] .tooltip-arrow {
    border-width: 5px 0 5px 5px;
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: transparent;
    right: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
  }

</style>


<link href='fullcalendar/packages/core/main.css' rel='stylesheet' />
<link href='fullcalendar/packages/daygrid/main.css' rel='stylesheet' />
<link href='fullcalendar/packages/list/main.css' rel='stylesheet' />

<script src='fullcalendar/packages/core/main.js'></script>
<script src='fullcalendar/packages/daygrid/main.js'></script>
<script src='fullcalendar/packages/list/main.js'></script>

<script src='https://unpkg.com/popper.js/dist/umd/popper.min.js'></script>
<script src='https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js'></script>
	
<script >

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [ 'dayGrid', 'list' ],
    defaultView: 'listWeek',
    defaultDate: '2020-02-13',
	views: {
        listDay: { buttonText: 'list day' },
        listWeek: { buttonText: 'list week' },
        listMonth: { buttonText: 'list month' }
      },
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listWeek,listDay'
    },
	eventRender: function(info) {
      var tooltip = new Tooltip(info.el, {
        title: info.event.extendedProps.description,
        placement: 'top',
        trigger: 'hover',
        container: 'body'
      });
    },
    events: 
      <?php
	  echo $json_events;
	  ?>
    
  });

  calendar.render();
});

</script>

</head>
<body>
<div id='calendar'></div>


</body>
</html>