// Siteexport Admin Plugin Script
(function($){
	$(function(){
			
	
		if ( !$('form#siteexport').size() ) {
			return;
		}
		
		var siteexportadmin = function() {
		};
		
		var hasErrors = function(data, status) {
			return (status != 'undefined' && status != 200);
/*			return data.match(new RegExp("((runtime|fatal) error|[error])", "i"))
					|| (status != 'undefined' && status != 200);*/
		};

		(function(_){
			
			_.url = DOKU_BASE + 'lib/exe/ajax.php';
			_.suspendGenerate = false;
			_.allElements = 'form#siteexport :input:not([readonly]):not([disabled]):not([type=submit]):not(button)';

			_.generate = function() {
				
				if ( _.suspendGenerate ) { return; }
				
				this.resetDataForNewRequest();

				_.throbber(true);
				$.post( _.url, _.settings('__siteexport_generateurl'), function(data, textStatus, jqXHR) {
					data = data.split("\n");
					$('#copyurl').val(data[0]);
					$('#wgeturl').val(data[1]);
					$('#curlurl').val(data[2]);
					_.updateCronStatusExists(data[3] == 'true');
				}).fail(function(jqXHR){
					_.errorLog(jqXHR.responseText);
				}).always(function(){
					_.throbber(false);
				});
			};
			
			_.run = function() {
			
				this.resetDataForNewRequest();
			
				_.throbber(true);
				$.post( _.url, _.settings('__siteexport_getsitelist'), function(data, textStatus, jqXHR) {
					data = data.split("\n");

					_.pattern = data.shift();
					_.zipURL = data.shift();

					_.pageCount = data.length - 1; // starting at 0
					_.currentCount = 0;

					_.allPages = data;
					_.status(_.pages());
					_.nextPage();
				}).fail(function(jqXHR){
					_.errorLog(jqXHR.responseText);
				}).always(function(){
					_.throbber(false);
				});
			};
					
			_.addSite = function(site) {
		
				var settings = _.settings('__siteexport_addsite');
				settings.site = site;
				settings.pattern = this.pattern;
				settings.base = DOKU_BASE;

				_.throbber(true);
				$.post( _.url, settings, function(data, textStatus, jqXHR) {
					_.zipURL = data.split("\n").pop();
					_.nextPage();
				}).fail(function(jqXHR){
					_.errorLog(jqXHR.responseText);
					_.errorCount++;
				}).always(function(){
					_.throbber(false);
				});
			};
			
			_.nextPage = function() {
				if (!this.allPages) {
					return;
				}
		
				var page = this.allPages.shift();
		
				if (!page) {
					_.status('Finished');
					if (_.zipURL != "" && _.zipURL != 'undefined' && typeof _.zipURL != 'undefined' ) {
						
						var frame = $('<iframe>')
							.hide()
							.prop({
								type : 'application/octet-stream',
								src : window.location.origin + _.zipURL
							})
							.load(function(e){
								_.status('Download completed.');
								frame.remove();
							}).appendTo('#siteexport');
						_.status('Starting Download.');
					} else {
						_.status('Finished - download failed. Please check your settings.');
						_.errorLog(_.zipURL);
					}
					return;
				}
		
				if (!page) {
					this.nextPage();
				}
		
				_.status('Adding "' + page + '" ' + this.pages(this.currentCount++));
				_.addSite(page);
			};
			
			_.pages = function() {
				return '( '
						+ _.currentCount
						+ ' / '
						+ _.pageCount
						+ (_.errorCount && _.errorCount != 0 ? ' / <span style="color: #a00">' + _.errorCount + '</span>'
								: '') + ' )';
			};
			
			_.status = function(text) {
				$('#siteexport__out').html(text);
			};
			
			_.settings = function(call) {
				var settings = {};
				settings.call = call;
				$.extend(settings, this.readValues());
				return settings;
			};
			
			_.putValue = function(object, name, value) {
				
				if ( name.indexOf('[]') > 0 ) {
					name = name.substr(0, name.indexOf('[]'));
					if ( typeof object[name] == 'undefined' ) {
						object[name] = [];
					}
					
					object[name].push(value);
				} else {
					object[name] = value;
				}
			};
			
			_.readValues = function() {
			
				var options = {};
				$(_.allElements).each(function(index, input) {
				
					switch(this.type) {
						case 'checkbox': if (this.checked) { _.putValue( options, this.name, $(this).val() ); } break;
						case 'select-one': 
						case 'select': {
							if ( $(this).val() != $(this.options[this.selectedIndex]).text() ) {
								_.putValue( options, this.name, parseInt($(this).val()));
 							} else {
								 _.putValue( options, this.name, $(this).val());
							}
							break;
						}
						default: _.putValue( options, this.name, $(this).val() );
					}
				});
				
				return options;
			};
			
			/**
			 * Display the loading gif
			 */
			_.throbberCount = 0;
			_.throbber = function(on) {
				
				_.throbberCount += (on?1:-1);
				$('#siteexport__throbber').css('visibility', _.throbberCount>0 ? 'visible' : 'hidden');
			};

			_.resetDataForNewRequest = function() {
				
				this.pageCount = 0;
				this.currentCount = 0;
				this.errorCount = 0;
				this.allPages = [];
				this.zipURL = '';
				this.pattern = '';

				this.status('');
				this.resetErrorLog();
			};

			/**
			 * Log errors into container
			 */
			_.errorLog = function(text) {
		
				if (!text) {
					return;
				}
		
				if (!$('#siteexport__errorlog').size()) {
					$('#siteexport__out').parent().append($('<div id="siteexport__errorlog"></div>'));
				}
		
				var msg = text.split("\n");
				for ( var int = 0; int < msg.length; int++) {
		
					var txtMsg = msg[int];
					txtMsg = txtMsg.replace(new RegExp("^runtime error:", "i"), "");
		
					if (txtMsg.length == 0) {
						continue;
					}

					$('#siteexport__errorlog').append($('<p></p>').text(txtMsg.replace(new RegExp(
							"</?.*?>", "ig"), "")));
				}
			};
		
			_.resetErrorLog = function() {
				$('#siteexport__errorlog').remove();
			};
			
			_.toggleDisableAllPlugins = function(input) {
				$('#siteexport :input[name=disableplugin\\[\\]]:not([disabled=disabled])').prop('checked', input.checked);
			};

	
			_.addCustomOption = function(nameVal, valueVal) {
				
				if ( typeof nameVal == 'undefined' )
				{
					nameVal = 'name';
				}
				
				if ( typeof valueVal == 'undefined' )
				{
					valueVal = 'value';
				}
		
				
				var regenerate = function(event) {
					event.stopPropagation();
					_.generate();
				};

				var name = $('<input/>').addClass('edit').attr({ name: 'customoptionname[]', value: nameVal}).change(regenerate).click(function(){this.select()});
				var value = $('<input/>').addClass('edit').attr({ name: 'customoptionvalue[]', value: valueVal}).change(regenerate).click(function(){this.select()});
				
				$('<li/>').append(name).append(value).appendTo('#siteexport__customActions');
			};
			
			_.cronAction = function(action, cronExists, successstatus) {
				_.resetDataForNewRequest();
				_.throbber(true);
				
				$.post( this.url, _.settings(action), function(data){
					_.updateCronStatusExists(cronExists, true);
					_.status('Successfully <b>'+successstatus+'</b> the Cron Job');
				}).fail(function(jqXHR){
					_.errorLog(jqXHR.responseText);
				}).always(function(){
					_.throbber(false);
				});
			};

			_.saveCron = function() {
				_.cronAction('__siteexport_savecron', true, 'saved');
			};
			
			_.deleteCron = function() {
				_.cronAction('__siteexport_deletecron', false, 'deleted');
			};

		
			_.updateCronStatusExists = function(cronExists, updateDeleteButton) {
		
				// Cron is not enabled.
				if (!$('form#siteexport :input[type=submit][name=do\\[cronSaveAction\\]]').size()) {
					return;
				}
		
				if (!updateDeleteButton && updateDeleteButton !== false) {
					updateDeleteButton = true;
				}
		
				$('#cronSaveAction').attr('disabled', cronExists);
		
				if (updateDeleteButton) {
					$('#cronDeleteAction').css( 'display', cronExists ? 'block' : 'none');
		
					$('#cronOverwriteExisting').change(function(event) {
						event.stopPropagation();
						_.updateCronStatusExists($('form#siteexport :input[type=submit][name=do\\[cronSaveAction\\]]').attr(
								'disabled') != 'disabled', false);
					});
				}
			};
			
			_.updateValue = function( elem, value ) {
				if ( !elem.size() ) {
					return;
				}
				switch(elem[0].type) {
					case 'checkbox': elem.prop('checked', elem.val() == value); break;
					case 'select-one': 
					case 'select': {
					
						var selected = false;
						elem.find('option').each(function(){
							if ( $(this).val() == value ) {
								$(this).prop('selected', true);
								selected = true;
								return false;
							}
						});
						
						if ( !selected && !isNaN(value) ) {
							elem.find('option:eq('+value+')').prop('selected', true);
						}
						
						 break;
					}
					default: elem.val(value);
				}
			};
			
			_.setValues = function(values) {
				
				$(_.allElements + ':not(:checkbox)').val(null);
				$(_.allElements + ':checkbox').prop('checked', false);
				$('#siteexport__customActions').html("");
				
				_.suspendGenerate = true;
				for ( var node in values ) {
					
					var value = values[node];
					var elem = null;
					
					if ( typeof value == 'object' ) {
						for ( var val in value ) {
							_.updateValue($('#siteexport #'+node+'_'+value[val]+':input[name='+node+'\\[\\]]'), value[val]);
						}
					} else {
						_.updateValue($('#siteexport :input[name='+node+']'), value);
					}
				}
				
				
				// Custom Options
				for ( var index in values['customoptionname'] ) {
					
					try {
						var name = values['customoptionname'][index];
						var value = values['customoptionvalue'][index]; 
						_.addCustomOption(name, value);
					} catch (e) {
						_.errorLog(e);
					}
				}
								
				_.suspendGenerate = false;
			};
			
			_.showCronJobs = function() {
				_.resetDataForNewRequest();
				_.throbber(true);
				
				$.post( this.url, _.settings('__siteexport_showcron'), function(data){
					
					data = $.parseJSON(data);
					if ( data === null ) {
						_.errorLog("No Valid CronData given.");
						return;
					}
					
					if ( !$('#siteexport__cronList').size() ) {
						$('#showcronjobs').parent().append($('<ul id="siteexport__cronList"/>'));
					} else {
						$('#siteexport__cronList').html("");
					}
						
					for ( var pattern in data) {
					
						var name = $('<span/>').text(pattern + ' ' + data[pattern]['ns']);
						var show = $('<button/>').addClass('button').attr('pattern', pattern).text('view').click(function(event) {
							event.stopPropagation();
							var values = data[this.getAttribute('pattern')];
							_.setValues(values);
							_.generate();
							
							return false;
						});
	
						$('<li/>').append(name).append(show).appendTo('#siteexport__cronList');
					}
					
					
				}).fail(function(jqXHR){
					_.errorLog(jqXHR.responseText);
				}).always(function(){
					_.throbber(false);
				});
			};

		}(siteexportadmin.prototype));
		
		var __siteexport = null;
		$.siteexport = function() {
			if ( __siteexport == null ) {
				__siteexport = new siteexportadmin();
			}
			
			return __siteexport;
		};
		
		$.siteexport().generate();
		$('#siteexport :input').each(function(index, element){
			$(this).change(function(event){
				event.stopPropagation();
				$.siteexport().generate();
			});
		});
		
		$('form#siteexport :input[type=submit][name~=do\\[siteexport\\]]').click(function(event){
			event.stopPropagation();
			$.siteexport().run();
			return false;
		});
		
		$('form#siteexport #depthType:input').change(function(event){
			event.stopPropagation();
			if ( parseInt($(this).val()) == 2 ) {
				$('div#depthContainer').show();
			} else {
				$('div#depthContainer').hide();
			}
		});
		

		$('form#siteexport #pdfExport:input').change(function(event){
			event.stopPropagation();
			$('form#siteexport #renderer').find('option[value=siteexport_pdf]').prop('selected', this.checked);
			$('form#siteexport #renderer').prop('disabled', this.checked);
		});
		
		$('form#siteexport #disableall:input').change(function(event){
			event.stopPropagation();
			$.siteexport().toggleDisableAllPlugins(this);
		});
		
		$('form#siteexport :input[name=disableplugin\\[\\]]').change(function(event){
			event.stopPropagation();
			
			if ( !this.checked && $('form#siteexport #disableall:input').prop('checked') ) {
				$('form#siteexport #disableall:input').prop('checked', false);
			}
		});
		
		$('form#siteexport :input[type=submit][name=do\\[addoption\\]]').click(function(event){
			event.stopPropagation();
			$.siteexport().addCustomOption();
			return false;
		});
		
		$('form#siteexport :input[type=submit][name=do\\[cronDeleteAction\\]]').click(function(event){
			event.stopPropagation();
			$.siteexport().deleteCron();
			return false;
		});
		
		$('form#siteexport :input[type=submit][name=do\\[cronSaveAction\\]]').click(function(event){
			event.stopPropagation();
			$.siteexport().saveCron();
			return false;
		});
		
		$('form#siteexport a#showcronjobs').click(function(event){
			event.stopPropagation();
			$.siteexport().showCronJobs();
			return false;
		});
		
		
	});
})(jQuery);