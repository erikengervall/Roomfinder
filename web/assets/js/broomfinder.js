(function() { /* Shell start */

	var app = angular.module('broom', ['ngAnimate', 'ui.bootstrap']); 

	app.controller('ButtonsCtrl', function () { /* Controller Start */

		/****
			Date functions
		****/
		/*
			Init date. Either to "today" or to previously starred.
		*/
		if (localStorage.getItem("starredDate") === null) {
			var date = "today"; // init date is today
		} else {
			var date = localStorage.getItem("starredDate"); // init date is starred
		}
		
		switchPressedDate(date);
		
		this.getDate = function(_date) {
			return (_date == date);
		};
		
		this.changeDate = function(_date) {
			date = _date;
//			alert("_date: " + _date + " || date: " + date);
			switchPressedDate(date);
		};
		
		/*
			Sets starred value to currently shown date
		*/
		this.setStarredDate = function() {
			if (typeof(Storage) !== "undefined") {
				if (localStorage.getItem("starredDate") === date) { /* If already fav -> remove fav. */
					localStorage.removeItem("starredDate");
					$(".starredDate").removeClass("fa-star");
					$(".starredDate").addClass("fa-star-o");
				} else {
					localStorage.setItem("starredDate", date);
					$(".starredDate").removeClass("fa-star-o");
					$(".starredDate").addClass("fa-star");
//					$("#angularTester").html(localStorage.getItem("starred"));
				}
			} else {
			    alert("Update your web browser to use the favorite functionality.");
			}
		}




		/****
			Location functions
		****/
		/*
			Init location. Either to "pol" or to previously starred.
		*/
		if (localStorage.getItem("starred") === null) {
			var location = "pol"; /* init location is polacks */
		} else {
			var location = localStorage.getItem("starred"); /* init location is starred */
		}
		

		/*
			Init active button, dropdown button text
		*/
		switchPressed(location);
		

		/*
			Sets starred value to currently shown location
		*/
		this.setStarred = function() {
			if (typeof(Storage) !== "undefined") {
				if (localStorage.getItem("starred") === location) { /* If already fav -> remove fav. */
					localStorage.removeItem("starred");
					$(".starred").removeClass("fa-star");
					$(".starred").addClass("fa-star-o");
				} else {
					localStorage.setItem("starred", location);
					$(".starred").removeClass("fa-star-o");
					$(".starred").addClass("fa-star");
					$("#angularTester").html(localStorage.getItem("starred"));
				}
			} else {
			    alert("Update your web browser to use the favorite functionality.");
			}
		}


		/*
			Is called from a ng-show. If it matches current location the correct tab will be shown.
			@return boolean
		*/
		this.matchLocation = function(_loc) {
			return (_loc == location);
		};


		/*
			Changes internal location.
			Updates trigger button text.
		*/
		this.changeLocation = function(_loc) {
			location = _loc;
			switchPressed(location); /* sets which button is active */
		};
		
		
		
		this.matchLocAndDate = function(_loc, _date) {
			return ((_loc == location) && (_date == date));
		};


		
		
	}); /* Controller end */
})(); /* Shell end */


/*
	Converts internal functionvariables to proper names for dropdown button text.
	@return location (now with proper name)
*/
function properNameConverter(_loc) {
	switch(_loc) {
	    case "pol":
	        _loc = "Polacks";
	        break;
	    case "pol_grp":
	        _loc = "Polacks, grupprum";
	        break;
	    case "ang":
	        _loc = "Ångan";
	        break;
	    case "ang_grp":
	        _loc = "Ångan, grupprum";
	        break;
	    default:
	}
	return _loc;
}


/*
	Updates active button (location)
*/
function switchPressed(_loc) {
	/* Active button */
	$(".loc-btn.btn-primary").removeClass("btn-primary");
	$("." + _loc + "-btn").addClass("btn-primary");

	/* Dropdown button text */
	$(".chooseCampusButton").html(properNameConverter(_loc) + "<span class='caret'></span>"); /* converts internal functionvariables to proper triggerbutton text, e.g. "pol" -> "Polacks" */
	
	if (localStorage.getItem("starred") !== _loc) { /* if not starred */
		$(".starred").removeClass("fa-star");
		$(".starred").addClass("fa-star-o");
	} else {
		$(".starred").removeClass("fa-star-o");
		$(".starred").addClass("fa-star");
	}
}



/*
	Converts internal functionvariables to proper names for dropdown button text.
	@return date (now with proper name)
*/
function properDateNameConverter(_date) {
	switch(_date) {
	    case "today":
	        _date = "Idag";
	        break;
	    case "tomorrow":
	        _date = "Imorgon";
	        break;
	    default:
	}
	return _date;
}



/*
	Updates active button (date)
*/
function switchPressedDate(_date) {
	/* Active button */
	$(".date-btn.btn-primary").removeClass("btn-primary");
	$("." + _date + "-btn").addClass("btn-primary");

	/* Dropdown button text */
	$(".chooseDateButton").html(properDateNameConverter(_date) + "<span class='caret'></span>"); /* converts internal functionvariables to proper triggerbutton text, e.g. "pol" -> "Polacks" */
	
	if (localStorage.getItem("starredDate") !== _date) { // if not starred
		$(".starredDate").removeClass("fa-star");
		$(".starredDate").addClass("fa-star-o");
	} else {
		$(".starredDate").removeClass("fa-star-o");
		$(".starredDate").addClass("fa-star");
	}
}




/********************************************************
	Not related to AngularJS
*********************************************************/
/*
	Executes whenever the document is ready.
*/
$(document).ready(function() {
	/*
		Slidedown/up animation for broom dropdown
	*/
	$('.dropdown').on('show.bs.dropdown', function(e) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown(100);
	});
	$('.dropdown').on('hide.bs.dropdown', function(e) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp(100);
	});
	
});








/********************************************************
	Stuff
*********************************************************/
/*
	$(".starred").addClass("fa-star-o");
	$(".starred").addClass("fa-star");
	$(".starred").removeClass("fa-star-o");
	$(".starred").removeClass("fa-star");
*/


/********************************************************
	Testing
*********************************************************/
/*
	$("#js-test").html("contentHeight" + contentHeight +
					"windowHeight" + windowHeight
					);
*/



















