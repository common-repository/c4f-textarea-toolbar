var c4f_admin = function() {

	// begin private section
	var names = new Array();
	names[0] = "strong.png";
	names[1] = "em.png";
	names[2] = "code.png";
	names[3] = "blockquote.png";
	names[4] = "acronym.png";
	names[5] = "link.png";
	
	function showButtonControls() {
		showContent(getButtonsPreviewContainer().id);
	}

	function showImageControls() {
		showContent(getImagesPreviewContainer().id);
	}

	function hideButtonControls() {
		hideContent(getButtonsPreviewContainer().id);
	}

	function hideImageControls() {
		hideContent(getImagesPreviewContainer().id);
	}

	function getImagesPreviewContainer() {
		return document.getElementById('icons');
	}

	function getButtonsPreviewContainer() {
		return document.getElementById('buttons');
	}
	
	function showContent(id) {
		var content = document.getElementById(id);
		if (content!=null) {
			content.style.display="inline";
		}
	}

	function hideContent(id) {
		var content = document.getElementById(id);
		if (content!=null) {
			content.style.display="none";
		}
	}	
	
	function loadImages(path) {
		var imagesContainer = getImagesPreviewContainer();
		if (imagesContainer == null) {
			return;
		}
		var images = imagesContainer.getElementsByTagName('img');
		for (i = 0; i < images.length; i++) {
			var current = images[i];
			current.src = path+"/"+names[i];
		}
	}	
	// end private section
	
	// begin public functions
	return {
		
		change:
		function(path, selectRef) {
			if (selectRef == null) {
				return;
			}
			var selectedOption = selectRef.options[selectRef.selectedIndex];
			if (selectedOption == null) {
				return;
			}
			
			var selectedValue = selectedOption.value;
			if (selectedValue.length>0) {
				// hide standard controls
				hideButtonControls();
				// load images
				loadImages(path+"/"+selectedValue);
				// show image-based controls
				showImageControls(selectedValue);
				
			} else {
				// hide images controls
				hideImageControls();
				// show standard controls
				showButtonControls();		
			}		
		},
		
		loadOnStartup:
		function(path, selectId) {
			var select = document.getElementById(selectId);
			if (select != null) {
				this.change(path, select);
			}
		}
	};
}();