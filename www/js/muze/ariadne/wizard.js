  var currentSection;

  function selectSection(section_id) {
    document.wgWizForm.wgWizNextStep.value=section_id;
    if (checksubmit()) {
		if (document.wgWizForm.onsubmit) {
			document.wgWizForm.onsubmit();
		}
	    document.wgWizForm.submit();
	}
  }
  
  function switchLanguage(nls) {
  	document.wgWizForm.arLanguage.value=nls;
    if (checksubmit()) {
		if (document.wgWizForm.onsubmit) {
			document.wgWizForm.onsubmit();
		}
    	document.wgWizForm.submit();
    }
  }
