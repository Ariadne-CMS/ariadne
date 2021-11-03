function vdOpenContextBar() {
	document.getElementById('vdContextBarClosed').style.display='none';
	vdContextBar=document.getElementById('vdContextBar');
	document.getElementById('vdContextBarOpen').style.display='block';
	document.getElementById('vdContextBar').style.height='100%';
	window_onresize();
}

function vdCloseContextBar() {
	vdContextBar=false;
	document.getElementById('vdContextBarClosed').style.display='block';
	document.getElementById('vdContextBarOpen').style.display='none';
	document.getElementById('vdContextBar').style.height='52px';
	window_onresize();
}

function vdGetProperty(input_id) {
	return vedor.widgets.properties.get(input_id);
}
function vdSetProperty(input_id, value) {
	return vedor.widgets.properties.set(input_id, value);
}
function vdEnableProperty(input_id) {
	return vedor.widgets.properties.enable(input_id); // FIXME: this func supports vararg.
}

function vdDisableProperty(input_id) {
	return vedor.widgets.properties.disable(input_id); // FIXME: this func supports vararg.
}

function vdPropertyIsEnabled(input_id) {
	return vedor.widgets.properties.isEnabled(input_id); 
}