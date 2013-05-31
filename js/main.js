//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

$(document).ready(function(){

$(window).politeWarning(10);

$('.deals-container').isotope({
  // options
  	itemSelector : '.deal-box',
  	layoutMode : 'masonry',
  	masonry : { 
   		columnWidth : 320 
  	}
});	

Parse.initialize("DaJkkAOKSFVxqPbI7gyPluuqRWkUgGIgzDzMJhUD", "X2oTRbTAnIRL3VVyI15JcSZJWs8BRqxxijudLJUq");
var deal_template_source = $('#deal-template').html(),
	dropdown_template_source = $('#dropdown-item-template').html(),
	deal_template = Handlebars.compile(deal_template_source),
	// dropdown_item_template = Handlebars.compile(dropdown_template_source),
	imgUrl = "http://www.photo-dictionary.com/photofiles/list/8/667airplane.jpg";

var TRIP = TRIP || {};

window.td = {pages: []};

moment.calendar = {
    lastDay : '[Yesterday]',
    sameDay : '[Today]',
    nextDay : '[Tomorrow]',
    lastWeek : '[last] dddd',
    nextWeek : 'dddd',
    sameElse : 'L'
};

mixpanel.track('Loaded Page');

$.extend(TRIP, {
	user_airport: "DTW",
	item_count: 0,
	getURLParameter: function (name) {
	    return decodeURI(
	        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	    );
	},
	locationString: function(str) {
		var parts = str.split(",");
		if (parts) {
			if (parts[1] != " DC") return parts[0];
		}
		return str;
	},
	setLocale: function() {
		var param = TRIP.getURLParameter("origin");
		if (param != 'null') {
			TRIP.user_airport = param.substring(0, param.length - 1);
		} else if ($.cookie('user_airport') != null) {
			TRIP.user_airport = $.cookie('user_airport');	
		}
		$.cookie('user_airport', TRIP.user_airport, { expires: 365 });
		var Cities = Parse.Object.extend("Cities"),
		city = new Cities();
		query = new Parse.Query(Cities);
		query.equalTo("active",true);
		query.equalTo("airport_code", TRIP.user_airport);
		query.find({
			success: function(results) {
				if (results && results.length > 0)
					$('#city_input').val(results[0].get("city") + ", " + results[0].get("state") + " (" + results[0].get("airport_code") + ") ");
					$('#city_input').attr("title",results[0].get("city") + ", " + results[0].get("state") + " (" + results[0].get("airport_code") + ") ");
			},
			error: function(results) {
			    alert("Oops! Something went wrong. The site may be experiencing some issues. Please refresh or visit again later.");

			}
		})
	},
	appendDeal: function(flight, styleName) {
		var aptCode = flight.get("destCode"),
		CityImages = Parse.Object.extend("CityImages"),
		cityImage = new CityImages(),
		cityQuery = new Parse.Query(CityImages);
      	cityQuery.equalTo("airportCode", aptCode);
      	cityQuery.first({
		  success: function(result) {
		  	imgUrl = result.get("imageUrl");
			var startDate =  moment(flight.get("departDate"), "MM/DD/YYYY"),
			returnDate = moment(flight.get("returnDate"), "MM/DD/YYYY"),
			numNights = returnDate.diff(startDate,'days'),
			dafact = ((Math.random() * 1.2) + 1.4),
			flightPrice = parseInt(flight.get("price")),
			hotelPrice = flight.get("hotel_price");
			if (hotelPrice == null) hotelPrice = 75; 
			
			var totalPrice = (hotelPrice * numNights) + flightPrice;
		  	var html = deal_template({
				destination: TRIP.locationString(flight.get("destLocation")), 
				bg: imgUrl,
				trip_length: "for " + numNights + ((numNights != 1) ? " Nights" : " Night"),
				leaving: startDate.calendar(),
				price: Math.round(flightPrice),
				old_price: parseInt(dafact * flightPrice),
				hotel_price: Math.round(hotelPrice),
				total_price: Math.round(totalPrice),
				buynowhref: flight.get("link"),
				booknowhref: flight.get("hotel_link")
			})
			$('.deal-box').first().addClass('large');
			$('.deals-container').isotope('insert', $(html));
			TRIP.item_count++;
			if (TRIP.item_count == TRIP.numCities) {
				$('.deal-box').click(function(){
					mixpanel.track('Enlarged Deal', $(this).find('.destination-name').text());
				 	$('.deal-box.large').removeClass('large');
				 	$(this).addClass('large');
				 	$('.deals-container').isotope('reLayout');
			 	});			
			}
			mixpanel.track_links(".buynow", "Click Buynow", function(ele) { return { type: $(ele).parent().find('.destination-name')}});
			mixpanel.track_links(".booknow", "Click Hotel", function(ele) { return { type: $(ele).parent().find('.destination-name')}});

		  },
		  error: function(error) {
		    alert("Oops! Something went wrong. The site may be experiencing some issues. Please refresh or visit again later.");
		  }
		});

	},
	getDeals: function() {
		var Deals = Parse.Object.extend("Deals"),
		deal = new Deals(),
		query = new Parse.Query(Deals);
		query.equalTo("originCode", TRIP.user_airport);
		query.ascending("departDate");
		query.count({
			success:function(count) {
				TRIP.numCities = count;
			},
			error: function(result) {
				//do nothing
			}
		});
		query.find({
		  success: function(results) {
		  	$.each(results, function() {
		  		TRIP.appendDeal(this);
		  	});

		  },
		  error: function(error) {
		    alert("Oops! Something went wrong. The site may be experiencing some issues. Please refresh or visit again later.");
		  }
		});
	},	
	getCities: function() {		
  		var Cities = Parse.Object.extend("Cities"),
		city = new Cities();
		query = new Parse.Query(Cities);
		query.equalTo("active",true);
		query.ascending("city");
		query.find({
			success: function(origin_cities) {
				blahBlah = [];
				$.each(origin_cities, function() {
					ac = this.get("airport_code");
					c = this.get("city")
					s = this.get("state")
					img = this.get("imageUrl")
					var i = {}
					i.title = ac;
					i.text = c + ", " + s;
					i.tags = ac + " " + c + " " + s + " (" + ac + ") ";
					i.thumb = img;
					i.loc = "?origin=" + ac + "/";
					blahBlah.push(i)
				})
				window.td.pages = blahBlah;
				if (!window.addedBLAH) {
					$('#city_input').searchdrop();
					window.addedBLAH = true
				}
		  		$.each(origin_cities, function() {
					// var html = dropdown_item_template({
					// 	airport_code: this.get("airport_code"), 
					// 	city: this.get("city"),
					// 	state: this.get("state")
					// });
					// $("#dropdown-cities ul").append(html);
				});
		  	},
		    error: function(error) {
		    	alert("Oops! Something went wrong. The site may be experiencing some issues. Please refresh or visit again later.");
		  	}
	    });
	}
});
 TRIP.setLocale();
 TRIP.getDeals();
 TRIP.getCities();

 var input = $('#city_input');

input.focus(function() {
     $(this).val('');
}).blur(function() {
     var el = $(this);

     /* use the elements title attribute to store the 
        default text - or the new HTML5 standard of using
        the 'data-' prefix i.e.: data-default="some default" */
     if(el.val() == '')
         el.val(el.attr('title'));
});

 $.fn.searchdrop = function(options) {

      var set = $.extend( {
      
           'show'                   : 5,
           'speed'                  : 200,
           'newWindow'              : false,
           'mode'                   : 'static',
           'contentLocation'        : 'searchdrop/searchdrop_content.json'
      
      }, options);
      
      return this.each(function() {
      
           var searchdrop_in = {
                pages: []
           };
           $.ajaxSetup({
                async: false
           });                 
                          
           if (set.mode == 'static')
           {
                searchdrop_in = $.extend({}, window.td);
                var i = 0;
           }

           $(this).keyup(function(event)
           {
                getsearchdrop($(this));
           });               
           
           function getsearchdrop($obj)
           {
                if ($obj.val())
                {
                     var out = '';
                     var c = 0;
                     for (var i = 0; i < searchdrop_in.pages.length; i++)
                     {
                          var pat = new RegExp($obj.val(), 'i');
                          if ((searchdrop_in.pages[i].title.search(pat) != -1 || searchdrop_in.pages[i].text.search(pat) != -1 || searchdrop_in.pages[i].tags.search(pat) != -1) && c < set.show)
                          {
                               out += '<a style="text-decoration:none;" href="' + searchdrop_in.pages[i].loc + '"';
                               if (set.newWindow)
                               {
                                    out += ' target="_blank"';
                               }
                               out += '><div style="background: url(' + searchdrop_in.pages[i].thumb + ');background-size:cover;" class="search_item_wrap"><div class="search_drop_item"><div class="search_drop_item_content"><div class="search_drop_item_content_title">' + searchdrop_in.pages[i].title + '</div><div class="search_drop_item_content_text">' + searchdrop_in.pages[i].text + '</div></div></div></div></a>';
                               c++;
                          }
                     }
                     if (c == 0)
                     {
                          out += '<div class="search_drop_no_items">Origin city not found. <a href="mailto:nowcation@umich.edu">Contact us</a> to add it.</div>';     
                     }
                                    
                     $('#search_drop_content').html(out);
                     $('#search_drop_content').fadeIn(set.speed);
                }
                else
                {
                     $('#search_drop_content').fadeOut(set.speed);     
                }
           }
           
           $('html').click(function() {
                $('#search_drop_content').fadeOut(set.speed);
           });
      
      });

 };
  
});	
