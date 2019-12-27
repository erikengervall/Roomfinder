<!DOCTYPE html>
<html ng-app="broom" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
	    
    <meta name="description" content="">
    <meta name="author" content="Erik Engervall">
	<meta name="keywords" content="HTML,CSS,XML,JavaScript,Development,App,Application,iOS,Web,Webb,Android,Klassrum,Broom,Kaffe,Classroom,CoffeCam,KaffeCam">
	
    <title>Broomfinder</title>
    <link rel="icon" href="/favicon.png">

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/broomfinder.css" rel="stylesheet">
  </head>
  <body ng-controller='ButtonsCtrl as btnctrl'>

<!-- Facebook -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/sv_SE/sdk.js#xfbml=1&version=v2.3";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>


<!-- Fixed navbar -->
<div class="navbar navbar-inverse navbar-fixed-top">
  <div id="nav" class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="http://engervall.com/"><i class="fa fa-home" aria-hidden="true"></i></a>
    </div>
  </div>
</div>

<!-- <div id="js-test"></div> -->


<?php
error_reporting(E_ALL);
setlocale(LC_TIME, "sv_SE");
/**
 *
 *
 * @TODO
 * - be able to paste in your own csv file from time edit and have it checked.
 * - #page has fixed height... terribru
 *
 *
 * x Some lessons ($lines) got more than one room, sort that out.
 *
 * @INFO
 * - [1] Starttid
 * - [3] Sluttid
 * - [8] Lokal
 */

/**
 * Room
 */
class Room
{
  public $room_number;
  public $booked = [];

  function setRoom($_room_number)
  {
    $this->room_number = $_room_number;
  }

  function getRoom()
  {
    return $this->room_number;
  }

  function setBooked($_booked)
  {
    $this->booked = $_booked;
  }

  function getBooked()
  {
    return $this->booked;
  }

  function addBookedTime($_start_time, $_end_time)
  {
    $booked_time = $_start_time . " - " . $_end_time;
    array_push($this->booked, $booked_time);
  }
}

/**
 *
 * insertsRoom
 *
 * @param array $Rooms  array with Room objects
 * @param array $freeAllDay  array with room_numbers that hasn't been added to $Rooms
 * @param var $start_time  start time of booking
 * @param var $end_time  end time of booking
 */
function insertRoom($Rooms, $freeAllDay, $room_number, $start_time, $end_time)
{
  $roomExist = false;

  foreach ($Rooms as $room_in_Rooms) {
    // Does room exist in $Rooms already?

    if ($room_number === $room_in_Rooms->getRoom()) {
      $roomExist = true;

      if (!doubleBooked($room_in_Rooms, $start_time, $end_time)) {
        // if it's a double booking, we don't have to add it at all.
        $room_in_Rooms->addBookedTime($start_time, $end_time);

        while (canMerge($room_in_Rooms->getBooked()) !== false) {
          // Merge
          $mergePosition = canMerge($room_in_Rooms->getBooked());
          $newBooked = doMerge($room_in_Rooms->getBooked(), $mergePosition);
          $room_in_Rooms->setBooked($newBooked);
        }
        break;
      }
    }
  } // /exist

  if ($roomExist === false) {
    // Does not exist in $Rooms -> new Room!
    ${"Room_" . $room_number} = new Room();
    ${"Room_" . $room_number}->setRoom($room_number);
    ${"Room_" . $room_number}->addBookedTime($start_time, $end_time);
    array_push($Rooms, ${"Room_" . $room_number});
    $freeAllDay = removeFromFreeAllDay($freeAllDay, $room_number);
  } // /new room

  if (empty($Rooms)) {
    ${"Room_" . $room_number} = new Room();
    ${"Room_" . $room_number}->setRoom($room_number);
    ${"Room_" . $room_number}->addBookedTime($start_time, $end_time);
    array_push($Rooms, ${"Room_" . $room_number});
    $freeAllDay = removeFromFreeAllDay($freeAllDay, $room_number);
  }

  return [$Rooms, $freeAllDay];
}

/**
 *
 * For testing purposes
 *
 * @param var $val  variable to print
 */
function doubleBooked($room_in_Rooms, $start_time, $end_time)
{
  $booked = $room_in_Rooms->getBooked();
  foreach ($booked as $aBook) {
    $aBook_START = intval(substr($aBook, 0, 2));
    $aBook_END = intval(substr($aBook, 8, 2));
    $new_START = intval(substr($start_time, 0, 2));
    $new_END = intval(substr($end_time, 0, 2));
    if ($new_START >= $aBook_START && $new_END <= $aBook_END) {
      return true;
    }
  }
  return false; // no double booking
}

/**
 *
 * For testing purposes
 *
 * @param var $val  variable to print
 */
function print_r2($val)
{
  echo '<pre>';
  print_r($val);
  echo '</pre>';
}

/**
 *
 * Checks for mergable strings inside Room array
 *
 * @param array $arr  Room->booked
 * @return index, index of Room->booked when merge is possible
 * @return false  no merge is possible
 */
function canMerge($arr)
{
  for ($s = 0, $ss = $s + 1; $ss < count($arr); $s++, $ss++) {
    if (substr($arr[$s], 8, 2) === substr($arr[$ss], 0, 2)) {
      return $s;
    }
  }
  return false;
}

/**
 *
 * Execute merge
 *
 * @param array $arr  Room->booked
 * @param var $z  index, index of Room->booked when merge is possible
 * @return boolean
 */
function doMerge($arr, $z)
{
  $zz = $z + 1;

  $merge_start = substr($arr[$z], 0, 8);
  $merge_end = substr($arr[$zz], 8, 5);
  $merge_result = $merge_start . $merge_end;

  unset($arr[$z]);
  unset($arr[$zz]);

  array_push($arr, $merge_result);
  $arr = array_values($arr);

  return $arr;
}

/**
 *
 * Invertes booked array
 *
 * @param array $arr  Room->booked
 * @param var $z  index, index of Room->booked when merge is possible
 * @return array  free array
 */
function free($_booked)
{
  // inverse of booked
  $free = [];
  $dasUberFree = [];

  for ($obj = 0; $obj < count($_booked); $obj++) {
    // obj
    $booked = $_booked[$obj]->getBooked();

    for ($i = 0, $ii = $i - 1; $i < count($booked); $i++, $ii++) {
      // booked

      if ($i === 0) {
        // first element
        if (substr($booked[$i], 0, 2) !== "08") {
          $free_time = "08:15" . " - " . substr($booked[$i], 0, 5);
          array_push($free, $free_time);
        }
      }

      if ($i > 0) {
        // inbetween
        $free_time =
          substr($booked[$ii], 8, 5) . " - " . substr($booked[$i], 0, 5);
        array_push($free, $free_time);
      }

      if ($i === count($booked) - 1) {
        // last element
        $free_time = substr($booked[$i], 8, 5) . " - framåt";
        array_push($free, $free_time);
      }
    } // /booked

    $_booked[$obj]->setBooked($free);
    $free = [];
    array_push($dasUberFree, $_booked[$obj]);
  } // /obj
  return $dasUberFree;
}

/**
 *
 * Removes a room from the freeAllDay array
 *
 * @param array $_freeAllDay  array with all free rooms
 * @param var $_room_number  room number of room to be removed
 * @return array  freeAllDay array
 */
function removeFromFreeAllDay($_freeAllDay, $_room_number)
{
  $i = array_search($_room_number, $_freeAllDay);

  if ($i !== false) {
    unset($_freeAllDay[$i]);
  }

  return $_freeAllDay;
}

/**
 *
 * Fixes object indexes issue when sorting on room number
 *
 * @param array $_free  array with all rooms showing free times
 * @return array  final array, sorted on room number and with correct object indexes
 */
function sortFree($_free)
{
  $_free_sorted = [];
  $count_free = count($_free);
  for ($i = 0; $i < $count_free; $i++) {
    array_push($_free_sorted, array_shift($_free));
  }
  return $_free_sorted;
}

/**
 *
 * Changes two objects place if return true
 *
 * @param $a  an object to compare
 * @param $b  another object to compare
 * @return boolean
 */
function compareRooms($a, $b)
{
  return $a->room_number - $b->room_number;
  //    return ($a->room_number < $b->room_number) ? -1 : 1;
}

/**
 *
 * Applies make up to horrid final array
 *
 * @param $_free_sorted  Final array, sorted on indexes and room
 */
function prettyPrint($_free_sorted, $ledigHelaDan, $location)
{
  // print_r2($_free_sorted); // RM
  if ($location === "pol") {
    // polacks
    echo "<div class='row'>";

    echo "<div id='Free-container'>";
    echo "<div id='Free-title'>Ledigt hela dagen";
    echo "</div>";
    if (empty($ledigHelaDan)) {
      echo "<div id='Free-room-number'>";
      print_r("Inga lediga rum");
      echo "</div>";
    } else {
      echo "<div id='Free-room-number'>";
      $imploded = implode("<br>", $ledigHelaDan);
      print_r($imploded);
      echo "</div>";
    }
    echo "</div>";

    $house2begin = -1;
    foreach ($_free_sorted as $room_in_free_sorted) {
      // when do we encounter house 2?
      $house2begin++;
      if ("2" === substr($room_in_free_sorted->getRoom(), 0, 1)) {
        break;
      }
    } // /house2

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Hus 1";
    echo "</div>";
    for ($i = 0; $i < $house2begin; $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='Room-free-times'>";
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Hus 2";
    echo "</div>";
    for ($house2begin; $i < count($_free_sorted); $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='free-times'>";
      if (empty($_free_sorted[$i]->getBooked())) {
        echo "Fullbokat";
      }
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "</div>";
  } // /polacks

  if ($location === "ang") {
    // ångan
    echo "<div class='row'>";

    echo "<div id='Free-container'>";
    echo "<div id='Free-title'>Ledigt hela dagen";
    echo "</div>";
    if (empty($ledigHelaDan)) {
      echo "<div id='Free-room-number'>";
      print_r("Inga lediga rum");
      echo "</div>";
    } else {
      echo "<div id='Free-room-number'>";
      $imploded = implode("<br>", $ledigHelaDan);
      print_r($imploded);
      echo "</div>";
    }
    echo "</div>";

    $corridor4begin = -1;
    foreach ($_free_sorted as $room_in_free_sorted) {
      // when do we encounter corridor 4?
      $corridor4begin++;
      if ("4" === substr($room_in_free_sorted->getRoom(), 0, 1)) {
        break;
      }
    } // /house2

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Korridor 2";
    echo "</div>";
    for ($i = 0; $i < $corridor4begin; $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='Room-free-times'>";
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Korridor 4";
    echo "</div>";
    for ($corridor4begin; $i < count($_free_sorted); $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='free-times'>";
      if (empty($_free_sorted[$i]->getBooked())) {
        echo "Fullbokat";
      }
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "</div>";
  } // /ångan

  if ($location === "pol_grp") {
    // pol_grp
    echo "<div class='row'>";

    echo "<div id='Free-container'>";
    echo "<div id='Free-title'>Ledigt hela dagen";
    echo "</div>";
    if (empty($ledigHelaDan)) {
      echo "<div id='Free-room-number'>";
      print_r("Inga lediga rum");
      echo "</div>";
    } else {
      echo "<div id='Free-room-number'>";
      $imploded = implode("<br>", $ledigHelaDan);
      print_r($imploded);
      echo "</div>";
    }
    echo "</div>";

    $house2begin = -1;
    foreach ($_free_sorted as $room_in_free_sorted) {
      // when do we encounter house 2?
      $house2begin++;
      if ("2" === substr($room_in_free_sorted->getRoom(), 0, 1)) {
        break;
      }
    } // /house2

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Hus 1";
    echo "</div>";
    for ($i = 0; $i < $house2begin; $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='Room-free-times'>";
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>Hus 2";
    echo "</div>";
    for ($house2begin; $i < count($_free_sorted); $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='free-times'>";
      if (empty($_free_sorted[$i]->getBooked())) {
        echo "Fullbokat";
      }
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "</div>";
  } // /pol_grp

  if ($location === "ang_grp") {
    // ang_grp
    echo "<div class='row'>";

    echo "<div id='Free-container'>";
    echo "<div id='Free-title'>Ledigt hela dagen";
    echo "</div>";
    if (empty($ledigHelaDan)) {
      echo "<div id='Free-room-number'>";
      print_r("Inga lediga rum");
      echo "</div>";
    } else {
      echo "<div id='Free-room-number'>";
      $imploded = implode("<br>", $ledigHelaDan);
      print_r($imploded);
      echo "</div>";
    }
    echo "</div>";

    $house2begin = -1;
    foreach ($_free_sorted as $room_in_free_sorted) {
      // when do we encounter house 2?
      $house2begin++;
      if ("10" === substr($room_in_free_sorted->getRoom(), 0, 2)) {
        break;
      }
    } // /house2

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>";
    echo "</div>";
    for ($i = 0; $i < $house2begin; $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='Room-free-times'>";
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "<div class='col-xs-6'>";
    echo "<div id='Room-title'>";
    echo "</div>";
    for ($house2begin; $i < count($_free_sorted); $i++) {
      echo "<div id='Room-container'>";
      echo "<div id='Room-room-number'>";
      print_r($_free_sorted[$i]->getRoom());
      echo "</div>";
      echo "<div id='free-times'>";
      if (empty($_free_sorted[$i]->getBooked())) {
        echo "Fullbokat";
      }
      $imploded = implode("<br>", $_free_sorted[$i]->getBooked());
      print_r($imploded);
      echo "</div>";
      echo "</div>";
    }
    echo "</div>";

    echo "</div>";
  } // /ang_grp
}

/*
	URLer till alla scheman
	http://pastebin.com/9Az6Arcg
*/

/**
 *
 * Main function, most of the functionality is in here
 * Handles file stream
 *
 * @return array  all room objects in $Rooms and all fully free objects in $freeAllDay
 */
function freeMain($location, $date)
{
  // 1 freeMain
  $allRoomsInPol = [
    "1111",
    "1112",
    "1113",
    "1145",
    "1146",
    "1211",
    "1212",
    "1213",
    "1245",
    "1311",
    "1312",
    "1313",
    "2244",
    "2245",
    "2247",
    "2314",
    "2315",
    "2344"
  ];
  $allRoomsInPolGrp = [
    "1149",
    "1152",
    "1154",
    "1156",
    "2226",
    "2232",
    "2240",
    "2241",
    "2242"
  ];
  $allRoomsInAng = [
    "2001",
    "2002",
    "2003",
    "2004",
    "2005",
    "4001",
    "4003",
    "4004",
    "4005",
    "4006",
    "4007",
    "4101"
  ]; // , "4104", "10131", "10133", "10202", "10204"
  $allRoomsInAngGrp = [
    "4102",
    "4103",
    "4104",
    "2040",
    "2041",
    "2042",
    "2043",
    "2044",
    "2045",
    "2046",
    "10131",
    "10133",
    "10202",
    "10204",
    "10211",
    "10212",
    "10213",
    "10214",
    "10205-07",
    "10208-10"
  ];
  $allTheRooms = array_merge(
    $allRoomsInPol,
    $allRoomsInAng,
    $allRoomsInPolGrp,
    $allRoomsInAngGrp
  );

  $Rooms = [];
  $freeAllDay = []; // all rooms are initially put in this array

  if ($location === "pol") {
    if ($date == "today") {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ69Qm6Y0X2560y5Y59566206Q052Q9Y72Y6285590X05X99Y2226605Y968X206665262Y055Y7525Y692590XX2266152266955XY05296064X5Y6Y250X9552962X2906506503X50Y62560Y6537952X605Y6929Y9535862X56Y56X6503X02663059Y5XY90Z3766551Q72ofQc.csv",
        'r'
      );
      $freeAllDay = $allRoomsInPol;
    } else {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ69Qm6Y5X2560y5Y59566206Q052Q9Y72Y6285590X05X99Y2226605Y968X206665262Y055Y7525Y692590XX2266152266955XY05296064X5Y6Y250X9552962X2906506503X50Y62560Y6537952X605Y6929Y9535862X56Y56X6503X02663059Y5XYb0Za76Z551672QfQc69x3coQ55.csv",
        'r'
      );
      $freeAllDay = $allRoomsInPol;
    }
  }

  if ($location === "pol_grp") {
    if ($date == "today") {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m6XYX85ZZ00Qm8Y0X5560y3Y50586968Q659Q0Y79Y6565502X05X94Y3356665Y968X606465366Y055Y7565Y699590XX73660555Z97x5Xc657QQ0cofQY6b3aZ7655.csv",
        'r'
      );
      $freeAllDay = $allRoomsInPolGrp;
    } else {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m6XYX85ZZ00Qm8Y5X5560y3Y50586968Q659Q0Y79Y6565502X05X94Y3356665Y968X606465366Y055Y7565Y699590XX73660555Z97x5Xc657QQ0cofQY6b3aZ7655.csv",
        'r'
      );
      $freeAllDay = $allRoomsInPolGrp;
    }
  }

  if ($location === "ang") {
    if ($date == "today") {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ19Qm6Y0X8560y3Y59566206Q052Q9Y72Y6885592X05X96Y2286605Y965X906465269Y055Y0595Y692590XX926675o86Q955XYZ59960617bY6c250X75f2Q62X990659Z5Q9ax6Yc55.csv",
        'r'
      );
      $freeAllDay = $allRoomsInAng;
    } else {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ19Qm6Y5X8560y3Y59566206Q052Q9Y72Y6885592X05X96Y2286605Y965X906465269Y055Y0595Y692590XX926675o86Q955XYZ59960617bY6c250X75f2Q62X990659Z5Q9ax6Yc55.csv",
        'r'
      );
      $freeAllDay = $allRoomsInAng;
    }
  }

  if ($location === "ang_grp") {
    if ($date == "today") {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ59Qm6Y0X0560y9Y59566206Q052Q9Y72Y6055598X05X94Y3306605Y963X006165360Y055Y2505Y693590XX5366653766955XY05596062X5Y6Y350X9553968X5926556508X50Y63560Y6555953X605Y6339Y9555163X56Y52X6505X03664559Y5fY570XX66o500539YQx69Z6736Z5Q669665035X6b5cYca56Q5.csv",
        'r'
      );
      $freeAllDay = $allRoomsInAngGrp;
    } else {
      $file = fopen(
        "https://se.timeedit.net/web/uu/db1/schema/ri1m0XYX65ZZ59Qm6Y5X0560y9Y59566206Q052Q9Y72Y6055598X05X94Y3306605Y963X006165360Y055Y2505Y693590XX5366653766955XY05596062X5Y6Y350X9553968X5926556508X50Y63560Y6555953X605Y6339Y9555163X56Y52X6505X03664559Y5fY570XX66o500539YQx69Z6736Z5Q669665035X6b5cYca56Q5.csv",
        'r'
      );
      $freeAllDay = $allRoomsInAngGrp;
    }
  }

  $i = 0;
  while (($line = fgetcsv($file)) !== false) {
    // 2 while
    // print_r2($line);
    if ($i <= 4) {
      $i++;
    } // jump over unimportant/empty line

    if ($i >= 5) {
      // 3
      $start_time = $line[1];
      $start_time = substr($start_time, 1, 3) . "15";

      $end_time = $line[3];
      $end_time = substr($end_time, 1, 5);

      $room_number = $line[8];
      $room_number = preg_replace("/[^0-9-,]/", "", $room_number);

      while (strlen($room_number) > 3) {
        // Checks if first 4 characters are in $freeAllDay array.
        // print_r2($room_number);

        if ($room_number[0] == ',') {
          $room_number = substr($room_number, 1, strlen($room_number));
        } else {
          $commaPos = strpos($room_number, ',');
          $single_room_number = substr($room_number, 0, $commaPos);
          // print_r2($single_room_number);
          if (in_array($single_room_number, $allTheRooms)) {
            $arrays = insertRoom(
              $Rooms,
              $freeAllDay,
              $single_room_number,
              $start_time,
              $end_time
            ); // returnerar $Rooms och $freeAllDay
            // print_r2("ADDED: " . $single_room_number);
            $Rooms = $arrays[0];
            $freeAllDay = $arrays[1];
            $room_number = substr(
              $room_number,
              $commaPos,
              strlen($room_number)
            ); // Remove the room we just added
          } else {
            if ($commaPos == false) {
              break; // If there's no more commas - and the current room is wrong. Break it.
            }
            //print_r2("COMMAPOS: " . $commaPos);
            $room_number = substr(
              $room_number,
              $commaPos,
              strlen($room_number)
            ); // Remove the room we can't match to our arrays with choosen rooms
            //print_r2("REMOVE THIS ROOM!!!: " . $room_number);
          }
        } // if first char i comma

        // if ($room_number[0] == ',') {
        // 	$room_number = substr($room_number, 1, strlen($room_number));
        // } else {
        // 	echo "Error 001: room_number | Felanmäl gärna till erik@brolutions.com.";
        // 	exit;
        // }
      } // while checks if first 4 chars..
    } // /3
  } // /2 while
  fclose($file); // fclose
  return [$Rooms, $freeAllDay];
} // /1 freeMain

echo "<div id='page'>"; // page START

/* AngularJS-Testing */
/* echo "<div style='color:#fff;height:50px;width:100px;' id='angularTester'></div>"; */

echo "<div class='row dropdown top' ng-show=" .
  "btnctrl.getDate('today')" .
  ">"; //style='border:1px solid blue;'
echo "<button class='btn btn-default chooseCampusButton' id='dLabel' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
echo "Välj område <span class='caret'></span>";
echo "</button>";
echo "<ul class='dropdown-menu filter-dropdowns' role='menu' aria-labelledby='dLabel'>";
echo "<button class='loc-btn pol-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('pol')" .
  ">Polacks</button>";
echo "<button class='loc-btn pol_grp-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('pol_grp')" .
  ">Polacks, grupprum</button>";
echo "<button class='loc-btn ang-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('ang')" .
  ">Ångan</button>";
echo "<button class='loc-btn ang_grp-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('ang_grp')" .
  ">Ångan, grupprum</button>";
echo "</ul>";
echo "<i ng-click=btnctrl.setStarred() class='starred fa fa-star-o'></i>";

echo "</div>";

echo "<div class='row dropdown top' ng-show=" .
  "btnctrl.getDate('tomorrow')" .
  ">";
echo "<button class='btn btn-default chooseCampusButton' id='dLabel' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
echo "Välj område <span class='caret'></span>";
echo "</button>";
echo "<ul class='dropdown-menu filter-dropdowns' role='menu' aria-labelledby='dLabel'>";
echo "<button class='loc-btn pol-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('pol')" .
  ">Polacks</button>";
echo "<button class='loc-btn pol_grp-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('pol_grp')" .
  ">Polacks, grupprum</button>";
echo "<button class='loc-btn ang-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('ang')" .
  ">Ångan</button>";
echo "<button class='loc-btn ang_grp-btn btn btn-default' ng-click=" .
  "btnctrl.changeLocation('ang_grp')" .
  ">Ångan, grupprum.</button>";
echo "</ul>";
echo "<i ng-click=btnctrl.setStarred() class='starred fa fa-star-o'></i>";

echo "</div>";

echo "<div class='row dropdown bot'>";
echo "<button class='btn btn-default chooseDateButton' id='dLabel' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
echo "Välj dag <span class='caret'></span>";
echo "</button>";
echo "<ul class='dropdown-menu' role='menu' aria-labelledby='dLabel'>";
echo "<button class='date-btn today-btn btn btn-default' ng-click=" .
  "btnctrl.changeDate('today')" .
  ">Idag (" .
  utf8_encode(strftime("%A")) .
  ")</button>"; //ucwords() for capital first letter
echo "<button class='date-btn tomorrow-btn btn btn-default' ng-click=" .
  "btnctrl.changeDate('tomorrow')" .
  ">Imorgon (hint: inte " .
  utf8_encode(strftime("%A")) .
  ")</button>";
echo "</ul>";
echo "<i ng-click=btnctrl.setStarredDate() class='starredDate fa fa-star-o'></i>";

echo "<br><div class='fb-like' data-href='https://www.facebook.com/brolutions' data-layout='button_count' data-action='like' data-show-faces='true' data-share='true'></div>";

echo "</div>";

$campuses = [];
$campuses[0] =
  "These are my campuses. They are very lovely. Om nom nom campuses. So many rooms. Lovely :)";
$oneHour = 1800;
$timeCampusTxtChanged = filemtime('campuses.txt'); // checks when the file campuses.txt was last updated. returned in time() format.

//if (( time() > $timeCampusTxtChanged + $oneHour) ) { // if last update + 1hr => update
//print_r2("nu hämtas ny data");
//pol
$allRooms = freeMain("pol", "today"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[1] = $freeAllDay;
$campuses[2] = $free_sorted;

//pol_grp
$allRooms = freeMain("pol_grp", "today"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[3] = $freeAllDay;
$campuses[4] = $free_sorted;

//ang
$allRooms = freeMain("ang", "today"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[5] = $freeAllDay;
$campuses[6] = $free_sorted;

//ang_grp
$allRooms = freeMain("ang_grp", "today"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[7] = $freeAllDay;
$campuses[8] = $free_sorted;

/***
		Tomorrow
	***/
//pol (tomorrow)
$allRooms = freeMain("pol", "tomorrow"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[9] = $freeAllDay;
$campuses[10] = $free_sorted;

//pol_grp (tomorrow)
$allRooms = freeMain("pol_grp", "tomorrow"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[11] = $freeAllDay;
$campuses[12] = $free_sorted;

//ang (tomorrow)
$allRooms = freeMain("ang", "tomorrow"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[13] = $freeAllDay;
$campuses[14] = $free_sorted;

//ang_grp (tomorrow)
$allRooms = freeMain("ang_grp", "tomorrow"); // freeMain() -> [0] = $Rooms, [1] = $freeAllDay
$Rooms = $allRooms[0];
$freeAllDay = $allRooms[1];

$free = free($Rooms); // inverted booked
usort($free, 'compareRooms');
$free_sorted = sortFree($free);

$campuses[15] = $freeAllDay;
$campuses[16] = $free_sorted;

// SERIALIZE
$serializedData = serialize($campuses);
file_put_contents('campuses.txt', $serializedData);
//}

$recoveredData = file_get_contents('campuses.txt');
$unserializedData = unserialize($recoveredData);
echo "<div ng-show=" . "btnctrl.getDate('today')>"; // pol START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('pol')>";
$freeAllDay = $unserializedData[1];
$free_sorted = $unserializedData[2];
prettyPrint($free_sorted, $freeAllDay, "pol");
echo "</div>";
echo "</div>"; // /pol END

echo "<div ng-show=" . "btnctrl.getDate('today')>"; // pol_grp START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('pol_grp')>";
$freeAllDay = $unserializedData[3];
$free_sorted = $unserializedData[4];
prettyPrint($free_sorted, $freeAllDay, "pol_grp");
echo "</div>";
echo "</div>"; // pol_grp END

echo "<div ng-show=" . "btnctrl.getDate('today')>"; // ang START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('ang')>";
$freeAllDay = $unserializedData[5];
$free_sorted = $unserializedData[6];
prettyPrint($free_sorted, $freeAllDay, "ang");
echo "</div>";
echo "</div>"; // ang END

echo "<div ng-show=" . "btnctrl.getDate('today')>"; // ang_grp START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('ang_grp')>";
$freeAllDay = $unserializedData[7];
$free_sorted = $unserializedData[8];
prettyPrint($free_sorted, $freeAllDay, "ang_grp");
echo "</div>";
echo "</div>"; // ang_grp END

/*
	Tomorrow
*/
echo "<div ng-show=" . "btnctrl.getDate('tomorrow')>"; // pol tmrw START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('pol')>";
$freeAllDay = $unserializedData[9];
$free_sorted = $unserializedData[10];
prettyPrint($free_sorted, $freeAllDay, "pol");
echo "</div>";
echo "</div>"; // pol tmrw END

echo "<div ng-show=" . "btnctrl.getDate('tomorrow')>"; // pol_grp tmrw START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('pol_grp')>";
$freeAllDay = $unserializedData[11];
$free_sorted = $unserializedData[12];
prettyPrint($free_sorted, $freeAllDay, "pol_grp");
echo "</div>";
echo "</div>"; // pol_grp tmrw END

echo "<div ng-show=" . "btnctrl.getDate('tomorrow')>"; // pol tmrw START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('ang')>";
$freeAllDay = $unserializedData[13];
$free_sorted = $unserializedData[14];
prettyPrint($free_sorted, $freeAllDay, "ang");
echo "</div>";
echo "</div>"; // ang tmrw END

echo "<div ng-show=" . "btnctrl.getDate('tomorrow')>"; // pol tmrw START
echo "<div class='loc' ng-show=" . "btnctrl.matchLocation('ang_grp')>";
$freeAllDay = $unserializedData[15];
$free_sorted = $unserializedData[16];
prettyPrint($free_sorted, $freeAllDay, "ang_grp");
echo "</div>";
echo "</div>"; // ang_grp tmrw END

echo "</div>";

// page END
?>


	<!-- Scripts -->
    <script src="assets/js/jquery-2.1.3.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/angular.min.js"></script> <!-- angularJS -->
    <script src="assets/js/ui-bootstrap-tpls-0.12.0.min.js"></script> <!-- ui-bootstrap -->
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.28/angular-animate.js"></script> <!-- ngAnimate -->
    <script src="assets/js/all.js"></script>
    <script src="assets/js/broomfinder.js"></script>
  </body>
</html>