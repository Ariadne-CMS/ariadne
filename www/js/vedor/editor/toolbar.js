	function vdDisableButton(id) {
		document.getElementById(id).style.backgroundPositionY = '-36px';
		document.getElementById(id).style.color = '#9A9A9A';
		document.getElementById(id).style.borderColor = '#D5D6D7';
		document.getElementById(id).style.cursor = 'default';
		document.getElementById(id).style.backgroundColor = 'transparent';
	}

	function vdEnableButton(id) {
		document.getElementById(id).style.backgroundPositionY = '0px';
		document.getElementById(id).style.color = '#000000';
		document.getElementById(id).style.borderColor = '#8F8F8F';
		document.getElementById(id).style.cursor = 'pointer';
		try {
			document.getElementById(id).style.backgroundColor = 'inherit';
		} catch (e) {
			document.getElementById(id).style.backgroundColor = '';
		}
	}

	function vdHideButton(id) {
		document.getElementById(id).style.display = 'none';
	}
	function vdShowButton(id) {
		document.getElementById(id).style.display = 'block';
	}
	function vdIsButtonHidden(id) {
		return (document.getElementById(id).style.display!='block');
	}
	function vdIsButtonEnabled(id) {
		return (document.getElementById(id).style.color!='#9A9A9A');
	}

	function vdPressButton(id) {
		if (vdIsButtonEnabled(id)) {
			document.getElementById(id).style.backgroundPositionY = '-18px';
		}
	}	

	function vdDePressButton(id) {
		if (vdIsButtonEnabled(id)) {
			document.getElementById(id).style.backgroundPositionY = '0px';
		}
	}
