muze.require('muze.event', function() {
	muze.namespace('muze.form.calendar', function() {
		return {
			target : null,
			calendar : null,
			attach: function() {
				inputs = document.getElementsByTagName("INPUT");
				for (i=0; i<inputs.length; i++) {
					if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_calendar") != -1) {
						muze.event.attach(inputs[i], "focus", muze.form.calendar.execute);
					}
				}
			},
			execute : function() {
				muze.form.calendar.target = this;
				if (muze.form.calendar.calendar) {
					muze.form.calendar.calendar.show();
				} else {
					tomorrow = new Date();
					tomorrow.setTime(tomorrow.getTime() + (1000*3600*24)); // Add one day to today.

					muze.form.calendar.calendar = new YAHOO.widget.Calendar("calendar", "senddate_calendar", {mindate:tomorrow});
					// FIXME: NLS
					muze.form.calendar.calendar.cfg.setProperty("MONTHS_SHORT",   ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"]);   
					muze.form.calendar.calendar.cfg.setProperty("MONTHS_LONG",    ["Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December"]);
					muze.form.calendar.calendar.cfg.setProperty("WEEKDAYS_1CHAR", ["Z", "M", "D", "W", "D", "V", "Z"]);   
					muze.form.calendar.calendar.cfg.setProperty("WEEKDAYS_SHORT", ["Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za"]);   
					muze.form.calendar.calendar.cfg.setProperty("WEEKDAYS_MEDIUM",["Zon", "Maa", "Din", "Woe", "Don", "Vri", "Zat"]);   
					muze.form.calendar.calendar.cfg.setProperty("WEEKDAYS_LONG",  ["Zondag", "Maandag", "Dinsdag", "Woensdag", "Donderdag", "Vrijdag", "Zaterdag"]);   

					muze.form.calendar.calendar.render();
					muze.form.calendar.calendar.selectEvent.subscribe(muze.form.calendar.selectEvent, muze.form.calendar.calendar, true);  
				}
			},
			selectEvent : function() {
				if (muze.form.calendar.calendar.getSelectedDates().length > 0) {
					var selDate = muze.form.calendar.calendar.getSelectedDates()[0];

					// Pretty Date Output, using Calendar's Locale values: Friday, 8 February 2008
					var dStr = selDate.getDate();
					dStr = (dStr < 10 ? '0' : '') + dStr;

					var mStr = selDate.getMonth() + 1;
					mStr = (mStr < 10 ? '0' : '') + mStr;

					var yStr = selDate.getFullYear();
					muze.form.calendar.target.value = dStr + "-" + mStr + "-" + yStr;
				} else {
					muze.form.calendar.target.value = "";
				}
				muze.form.calendar.calendar.hide();
			}
		}
	});
});

muze.namespace( 'muze.form.cancelEnter', function() {
	return {
		attach : function() {
			forms = document.getElementsByTagName("FORM");
			for (i=0; i<forms.length; i++) {
				if (forms[i] && forms[i].className && forms[i].className.indexOf("muze_form_cancelenter") != -1) {
					inputs = forms[i].getElementsByTagName("INPUT");
					for (j=0; j<inputs.length; j++) {
						inputelm = inputs[j];
						muze.event.attach(inputelm, "keypress", muze.form.cancelenter.execute);
					}
				}
			}
		},
		execute : function(evt) {
			var keyCode = evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
			if (keyCode == 13) {
				muze.event.cancel(evt);
				return false;
				this.tabIndex
			}
		}
	}
});

muze.namespace( 'muze.form.clearOnFocus', function() {
	return {
		attach : function() {
			inputs = document.getElementsByTagName("INPUT");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_clearOnFocus") != -1) {
					inputelm = inputs[i];
					inputelm.initialvalue = inputelm.value;
					muze.event.attach(inputelm, "focus", muze.form.clearOnFocus.execute);
				}
			}
			divs = document.getElementsByTagName("DIV");
			for (i=0; i<divs.length; i++) {
				if (divs[i] && divs[i].className && divs[i].className.indexOf("muze_form_clearOnFocus") != -1) {
					inputs = divs[i].getElementsByTagName("INPUT");
					for (j=0; j<inputs .length; j++) {
						if (inputs [j]) {
							inputelm = inputs[j];
							inputelm.initialvalue = inputelm.value;
							muze.event.attach(inputelm, "focus", muze.form.clearOnFocus.execute);
						}
					}
				}
			}
					
		},
		execute : function() {
			if (this.value == this.initialvalue) {
				this.value = '';
			}
		}
	}
});

muze.namespace( 'muze.form.keyboardNumbers', function() {
	return {
		attach : function() {
			inputs = document.getElementsByTagName("INPUT");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_keyboardNumbers") != -1) {
					inputelm = inputs[i];
					muze.event.attach(inputelm, "keydown", muze.form.keyboardNumbers.execute); // IE does not fire keypress event for arrows.
				}
			}
		},
		execute : function(evt) {
			var keyCode = evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
			if (!isNaN(this.value)) {
				myvalue = parseInt(this.value);
				if (isNaN(myvalue)) {
					myvalue = 0;
				}
				if (keyCode == 38) { // keyboard arrow up
					myvalue++;
					this.value = myvalue;
					muze.event.fire(this, "change");
				}
				if (keyCode == 40) { // keyboard arrow down
					myvalue--;
					this.value = myvalue;
					muze.event.fire(this, "change");
				}
			}
		}
	}
});

muze.namespace( 'muze.form.numbersOnly', function() {
	return {
		attach: function() {
			inputs = document.getElementsByTagName("INPUT");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_numbersonly") != -1) {
					muze.event.attach(inputs[i], "keypress", muze.form.numbersOnly.execute);
				}
			}
		},
		execute : function(evt) {
			var keyCode = evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
			if (
				keyCode == 8 || 	// backspace
				keyCode == 9 || 	// tab
				keyCode == 46 ||	// keypad del
				(keyCode > 36 && keyCode < 41) // arrow keys
			) {
				return true;
			}
			if (keyCode<48 || keyCode > 57) { // if the key is not a number
				muze.event.cancel(evt);
				return false;
			}
		}
	}
});

muze.namespace( 'muze.form.subselection', function() {
	return {
		attach : function() {
			inputs = document.getElementsByTagName("DIV");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_show_subselection") != -1) {
					inputelm = inputs[i].getElementsByTagName("SELECT")[0];
					if (inputelm) {
						muze.event.attach(inputelm, "change", muze.form.subselection.execute);
						inputelm.execute = muze.form.subselection.execute;
						inputelm.execute();
					}
					radiooptions = inputs[i].getElementsByTagName("INPUT");
					for (j=0; j<radiooptions.length; j++) {
						if (radiooptions[j]) {
							muze.event.attach(radiooptions[j], "click", muze.form.subselection.execute);
							muze.event.attach(radiooptions[j], "change", muze.form.subselection.execute);

							radiooptions[j].execute = muze.form.subselection.execute;
							radiooptions[j].execute();
						}
					}
				}
			}			
		},
		execute : function() {
			inputs = this.form.getElementsByTagName("DIV");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_subselection") != -1) {
					inputelm = inputs[i];
					inputelm.style.display = "none";
				}
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf(this.value+"_subselection") != -1) {
					inputelm = inputs[i];
					inputelm.style.display = "block";
				}
			}
		}
	}
});

muze.namespace( 'muze.form.textareaMaxLength', function() {
	return {
		attach : function() {
			inputs = document.getElementsByTagName("TEXTAREA");
			for (i=0; i<inputs.length; i++) {
				if (inputs[i] && inputs[i].className && inputs[i].className.indexOf("muze_form_textareaMaxLength") != -1) {
					inputelm = inputs[i];
					muze.event.attach(inputelm, "keypress", muze.form.textareaMaxLength.execute);
				}
			}			
		},
		execute : function(evt) {
			// FIXME: This requires invalid HTML, because maxlength attribute does not exist for textarea.
			var maxLength = parseInt(this.getAttribute("maxlength"));
			var keyCode = evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;

			if (maxLength && (this.value.length >= maxLength) && (keyCode == 13 || keyCode >= 33)) {
				muze.event.cancel(evt);
				return false;
			}
		}
	}
});

