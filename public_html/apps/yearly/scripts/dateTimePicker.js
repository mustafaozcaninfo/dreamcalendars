
'use strict';


(function(factory) {
  if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // CommonJS
    factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function($) {
    
   
	var plugin_name = 'calendar',
			data_key = 'plugin_' + plugin_name,
			defaults = {
				modifier: 'datetimepicker', // wrapper class
				day_name: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
				day_first: 0,
				month_name: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				paging: ['<i class="prev"></i>', '<i class="next"></i>'],
				unavailable: [], // static data - to merge with dynamic data
				adapter: null, // host to get json data of unavailable date
				month: null, // month of calendar = month in js + 1, month in js = 0-11
				year: null, // year of calendar
				highlightWeekend: 0, //  Weekend highlight
				showWeekend: 0, //  Weekend highlight
			 	long_month_name: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				short_month_name: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
				useShortNames: 0,
				num_next_month: 0, // number of next month to show
				num_prev_month: 0, // number of prev month to show
				num_of_week: 6, // number of week need to show on calendar number/auto
				onSelectDate: function(date, month, year){}, // trigger on select
			};

	var Plugin = function(options){
		var date = new Date();

		this.options = options;
		this.$element = $(options.element);
		this.options.year = this.$element.data('year') || this.options.year || date.getFullYear();
		this.options.month = this.$element.data('month') || this.options.month || date.getMonth() + 1;
		this.options.month--;
 // for plugin
		this.unavailable = []; // for check for unavailable dates

		this.init.call(this);
		return this;
	};

	Plugin.prototype = {
		init: function(){
			var _this = this;
			
 	// re-sort day_name & create day_index
 	this.totalweek=0;
			this.day_index = [];
			this.options.day_name = this.options.day_name.slice(this.options.day_first).concat(this.options.day_name.slice(0, this.options.day_first));
			for (var i = 0; i < this.options.day_name.length; i++){
				this.day_index.push((this.options.day_first + i + 7) % 7);
			}
			this.update();
		},

		/**
		 * Update data, get json from adapter, generate search keywords & send to adapter
		 * @return {[type]} [description]
		 */
		update: function(){
			var _this = this, months = this.getMonths();

			// get data
			if (typeof this.options.adapter === 'string'){
				$.getJSON(this.options.adapter, {
					keys: $(months).map(function(item){
						// format search key from js to database store
						return this.year + '-' + (this.month <= 8?'0':'') + (this.month + 1);
					}).toArray()
				}, function(data){
					_this.unavailable = _this.options.unavailable.concat(data);
					_this.draw(months);
				});
			}else{
				this.unavailable = this.options.unavailable;
				this.draw(months);
			}
		},

		
		draw: function(months){
			this.$element.empty();
			for (var i in months){
				this.$element.append(this.getCalendar(months[i].month, months[i].year));
			}
		},

		/**
		 * Get list of months need to show
		 * @return {[type]} [description]
		 */
		getMonths: function(){
			var date = new Date(), result = [];
			for (var i = this.options.month - this.options.num_prev_month; i <= this.options.month + this.options.num_next_month; i++){
				date.setFullYear(this.options.year);
        		date.setDate(1);
				date.setMonth(i);
				result.push({
					month: date.getMonth(),
					year: date.getFullYear()
				});
			}
			return result;
		},

	
		prevMonth: function(){
			this.options.month--;
			this.update();
		},

		/**
		 * Load next month
		 * @return {[type]} [description]
		 */
		nextMonth: function(){
			this.options.month++;
			this.update();
		},

	
		getCalendar: function(month, year){
			var _this = this, date = new Date();
			date.setFullYear(year);
      		date.setDate(1);
			date.setMonth(month);
      			
			var day_first = date.getDay();

		 
			date.setMonth(date.getMonth() + 1);
			date.setDate(0);
			var total_date = date.getDate();

		 
			var date_start = this.day_index.indexOf(day_first);

		 
			var total_week;
			if (!isNaN(this.options.num_of_week)){
				total_week = 6;
				
			}else{
				total_week = Math.ceil((date_start + total_date)/7);
			}
  
			// draw
			return $('<div>').addClass(this.options.modifier).append([
				$('<div>').addClass('paging').append(function(){
					return [
						$('<span>').addClass('prev').append(_this.options.paging[0]).click(function(){
							_this.prevMonth();
						}),
						$('<div>').addClass('month-name').append([_this.options.month_name[date.getMonth()], ' ', date.getFullYear()]),
						$('<span>').addClass('next').append(_this.options.paging[1]).click(function(){
							_this.nextMonth();
						}),
					];
				}),
				$('<table>').append(function(){
				    
				     if (_this.options.showWeekend==1) {
				         return [ 	$('<thead>').append($('<td>').append("No.")).append(function(){
						      
							return $(_this.options.day_name).map(function(index, element){
								return $('<td>').append(element);
							}).toArray();
						}),
						$('<tbody>').append(function(){
							var ap = [];
							for (var i = 0; i < total_week; i++){
							    
							     
							
								ap.push($('<tr>').append(function(){
								    _this.totalweek=_this.totalweek+1;
								    
								 
									var ap = [];
									
									var cd = new Date();
										cd.setFullYear(year);
                    					cd.setDate( 1 );
                    					 cd.setMonth(month);
                    					 
                    					 //cd.setDate( (i*7) + 1 );
                    					 
                    				cd.setDate(cd.getDate() +  (i*7) + 1);
                    					
										
										
										console.log(i+"=="+cd);
										
										
									var weekOfYear = function(date){
                                                 var d = new Date(+cd);
                                                 d.setHours(0,0,0);
                                                  d.setDate(d.getDate()+4-(d.getDay()||7));
                                               return Math.ceil((((d-new Date(d.getFullYear(),0,1))/8.64e7)+1)/7);
                                            };
										ap.push($('<td class="weeknumber">').append(weekOfYear));
										
										
									for (var j = 0; j < 7; j++){
										var d = new Date();
										d.setFullYear(year);
                    					d.setDate(1);
										d.setMonth(month);
									
										//console.log(month, year);
										//d.setDate(1);
										d.setDate(-date_start + (j + 1)+(i*7));
										//var available = me.options.filter.call(me, d);
										ap.push($('<td>').addClass(function(){
									 
										    var cls = [];
										    if (_this.options.day_first==0){
										        if (_this.options.highlightWeekend==1&&(j==0||j==6)) {	cls.push('highlight');}
										    } else
										    
										    {
										        if (_this.options.highlightWeekend==1&&(j==5||j==6)) {	cls.push('highlight');}
										    }
										    
										    
										    
											
											if (_this.isAvailable(d.getDate(), d.getMonth() + 1, d.getFullYear())){
												cls.push('available');
											}else{
												cls.push('unavailable');
											}
											if (d.getMonth() == date.getMonth()){
												cls.push('cur-month');
											}else{
												cls.push('near-month');
											}
											
											
											return cls.join(' ');
										}).data({date: d.getDate(), month: d.getMonth() + 1, year: d.getFullYear()}).append(d.getDate()).click(function(){
											var date = $(this).data('date'),
											month = $(this).data('month'),
											year = $(this).data('year');
											_this.options.onSelectDate.call(_this, date, month, year);
										}));
									}
									return ap;
								}));	
							}
							return ap;
						})
					];
				     } else {
				         return [ 	$('<thead>').append(function(){
						      
							return $(_this.options.day_name).map(function(index, element){
								return $('<td>').append(element);
							}).toArray();
						}),
						$('<tbody>').append(function(){
							var ap = [];
							for (var i = 0; i < total_week; i++){
							    
							     
							
								ap.push($('<tr>').append(function(){
								    _this.totalweek=_this.totalweek+1;
								    
								 
									var ap = [];
									
									var cd = new Date();
										cd.setFullYear(year);
                    					cd.setDate( 1 );
                    					 cd.setMonth(month);
                    					 
                    					 //cd.setDate( (i*7) + 1 );
                    					 
                    				cd.setDate(cd.getDate() +  (i*7) + 1);
                    					
										
										
										console.log(i+"=="+cd);
										
										
									var weekOfYear = function(date){
                                                 var d = new Date(+cd);
                                                 d.setHours(0,0,0);
                                                  d.setDate(d.getDate()+4-(d.getDay()||7));
                                               return Math.ceil((((d-new Date(d.getFullYear(),0,1))/8.64e7)+1)/7);
                                            };
									//	ap.push($('<td class="weeknumber">').append(weekOfYear));
										
										
									for (var j = 0; j < 7; j++){
										var d = new Date();
										d.setFullYear(year);
                    					d.setDate(1);
										d.setMonth(month);
									
										//console.log(month, year);
										//d.setDate(1);
										d.setDate(-date_start + (j + 1)+(i*7));
										//var available = me.options.filter.call(me, d);
										ap.push($('<td>').addClass(function(){
									 
										    var cls = [];
										    if (_this.options.day_first==0){
										        if (_this.options.highlightWeekend==1&&(j==0||j==6)) {	cls.push('highlight');}
										    } else
										    
										    {
										        if (_this.options.highlightWeekend==1&&(j==5||j==6)) {	cls.push('highlight');}
										    }
										    
										    
										    
											
											if (_this.isAvailable(d.getDate(), d.getMonth() + 1, d.getFullYear())){
												cls.push('available');
											}else{
												cls.push('unavailable');
											}
											if (d.getMonth() == date.getMonth()){
												cls.push('cur-month');
											}else{
												cls.push('near-month');
											}
											
											
											return cls.join(' ');
										}).data({date: d.getDate(), month: d.getMonth() + 1, year: d.getFullYear()}).append(d.getDate()).click(function(){
											var date = $(this).data('date'),
											month = $(this).data('month'),
											year = $(this).data('year');
											_this.options.onSelectDate.call(_this, date, month, year);
										}));
									}
									return ap;
								}));	
							}
							return ap;
						})
					];
				     }
					
				})

			]);
		},

		/**
		 * Check the date book: date, month, year have to in MySql format
		 * Base on this.unavailable data / input MySql date format (support glob)
		 * Eg: ['year-month-date', '*-month-date']
		 * @param  {[type]}  date  [description]
		 * @param  {[type]}  month month in javascript = 0-11, month in MySql = 1-12, so month input have to +1 before run this function
		 * @param  {[type]}  year  [description]
		 * @return {Boolean}       [description]
		 */
		isAvailable: function(date, month, year){
			for (var i in this.unavailable){
				var book_date = this.unavailable[i].split('-');
				if (book_date.length !== 3){
					return false;
				}else if (
					(book_date[0] == '*' || book_date[0] - year === 0)	&&
					(book_date[1] == '*' || book_date[1] - month === 0) &&
					(book_date[2] == '*' || book_date[2] - date === 0)
				){
					return false;
				}
			}
			return true;
		},
	};

	$.fn[plugin_name] = function (options){
		return this.each(function(){
			var data = $(this).data(data_key);
			if (!data){
				$(this).data(data_key, new Plugin($.extend({
					element: this
				}, defaults, options)));
			}
			if (typeof options === 'string'){
				$(this).data(data_key)[options].call(this);
			}
		});
	};
}));