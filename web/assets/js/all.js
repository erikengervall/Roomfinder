/*
	Closes the Responsive Menu on scroll (especially awesome for mobile)
*/
$(window).scroll(function() {
/*
	if ($(".collapse").hasClass("in")) { // If menu visable
		$("#nav-icon").toggleClass("open"); // Hamburger
		$(".collapse").collapse("hide"); // Actual menu
		if ($(".navbar-nav > .dropdown").hasClass("open")) { // http://stackoverflow.com/questions/10941540/how-to-hide-twitter-bootstrap-dropdown
			$(".navbar-nav > .dropdown").toggleClass("open"); // Dropdown menu
		}
	}
*/
});


/*
	Resize to fix dropdown in navbar
*/
$(window).on("resize", function() {
	if ($(".collapse").hasClass("in")) { // If menu visable
		$("#nav-icon").toggleClass("open"); // Hamburger
		$(".collapse").collapse("hide"); // Actual menu
	}

	if ($(".dropdown.open").hasClass("open")) {
		$('.dropdown.open .dropdown-toggle').dropdown('toggle');
		console.log("if-resize");
	}
	console.log("resize");
});


/*
	Hamburger menu flippidyflipp (borrowed). onClick toggleClass
*/
$("#nav-icon").click(function(){
	$(this).toggleClass("open");
});


/*
	Closes the Responsive Menu on Menu Item Click @MFU
*/
/*
$(".navbar-collapse ul li a").on("click", function(e) {
	if ($(e.target).hasClass("dropdown-toggle")) {
		if ($("#der-uber-spy").hasClass("collapse")) {
//			console.log("hehe");
		}
	}
//	console.log($(e.target));
//    $(".navbar-toggle:visible").click();
});
*/


/*
	jQuery for page scrolling feature - requires jQuery Easing plugin. Similar to Smoothscroll.js.
*/
$(function() {
    $("a.page-scroll").bind("click", function(event) {
        var $anchor = $(this);
        $("html, body").stop().animate({
            scrollTop: $($anchor.attr("href")).offset().top
        }, 500, "linear");
        event.preventDefault();
    });
});







/*
	Syntax
*/
/*
>>Log
console.log( "end" );


>>jQuery scroll
$(window).scroll(function() {


>>read
$(document).ready(function() {
 // executes when HTML-Document is loaded and DOM is ready

>>load
$(window).load(function() {
 // executes when complete page is fully loaded, including all frames, objects and images



*/















