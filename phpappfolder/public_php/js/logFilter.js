/*
* jQuery table searcher - used to filter logs
* Based on: https://stackoverflow.com/questions/12433304/live-search-through-table-rows
* Author: D Hollis <p2533140@my365.dmu.ac.uk> (Team 21-3110-AS)
 */
$(document).ready(function() {
	  $(".search").keyup(function () {
	    var searchTerm = $(".search").val();
	    var listItem = $('.results tbody').children('tr');
	    var searchSplit = searchTerm.replace(/ /g, "'):containsi('")
	    
	  $.extend($.expr[':'], {'containsi': function(elem, i, match, array){
	        return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
	    }
	  });
	    
	  $(".results tbody tr").not(":containsi('" + searchSplit + "')").each(function(e){
	    $(this).hide();
	  });
	  $(".results tbody tr:containsi('" + searchSplit + "')").each(function(e){
	    $(this).show();
	  });
	      });
	});