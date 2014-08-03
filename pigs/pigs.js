/* 
 * PIGS 4.0
 * 
 * FireDart Studios
 * Copyright 2014, MIT License
 * 
 */
function Pigs(imagesPath) {
	// If no imagesPath is provided abort
	if (!imagesPath) {
		console.warn('Pigs needs a selector path to work, for example:');
		console.warn('window.myPigs = new Pigs(".pigs_gallery ul.pigs_images a");');
		return;
	}
	
	/* 
	 * findImages()
	 * 
	 * Find all the images and adds an event handler
	 */
	function findImages() {
		var images = document.querySelectorAll(imagesPath);
		
		for (var i = 0; i < images.length; i++) {
			images[i].addEventListener("click", model, false);
		}
	}
	
	/* 
	 * model()
	 * 
	 * Opens the model window
	 */
	function model(event) {
		event.preventDefault();
		
		var href = this.href,
			winW,
			winH,
			modelBox,
			cvOverlay,
			cvContent,
			cvInner,
			cvImage,
			cvNext,
			cvPrev,
			cvOriginal,
			galleryId,
			parent,
			next,
			prev,
			currentIndex,
			loadingImage,
			imgW,
			imgH,
			ratio,
			imgMW,
			imgMH;
		
		// Get page dimensions
		if (document.body && document.body.offsetWidth) {
			winW = document.body.offsetWidth;
			winH = document.body.offsetHeight;
		}
		if (document.compatMode=='CSS1Compat' &&
			document.documentElement &&
			document.documentElement.offsetWidth) {
			winW = document.documentElement.offsetWidth;
			winH = document.documentElement.offsetHeight;
		}
		if (window.innerWidth && window.innerHeight) {
			winW = window.innerWidth;
			winH = window.innerHeight;
		}		
		
		// Append model if needed, save doms
		if(document.getElementById('contentviewer') !== null) {
			document.getElementById('contentviewer').style.display = '';
			
			modelBox = document.getElementById('contentviewer');
			cvOverlay = document.getElementById('cv-overlay');
			cvContent = document.getElementById('cv-content');
			cvInner = document.getElementById('cv-inner');
			cvImage = document.getElementById('cv-image');
			
			cvNext = document.getElementById('cv-next');
			cvPrev = document.getElementById('cv-prev');
			
			cvOriginal = document.getElementById('cv-original');
			
		} else {
			// Make modelBox
			modelBox = document.createElement("div");
			modelBox.id = "contentviewer";
			
				cvOverlay = document.createElement("div");
				cvOverlay.id = "cv-overlay";
				
				cvContent = document.createElement("div");
				cvContent.id = "cv-content";
				
					cvInner = document.createElement("div");
					cvInner.id = "cv-inner";
					cvContent.appendChild(cvInner);
					
						cvImage = document.createElement("img");
						cvImage.id = "cv-image";
						cvInner.appendChild(cvImage);
						
				cvNext = document.createElement("a");
				cvNext.id = "cv-next";
				cvNext.target = "_blank";
				cvNext.innerHTML = "&#9658;"; // &#9658; sm: &#9654;
				
				cvPrev = document.createElement("a");
				cvPrev.id = "cv-prev";
				cvPrev.target = "_blank";
				cvPrev.innerHTML = "&#9668;"; // &#9668; sm: &#96648;
				
				if(this.dataset.original) {
					cvOriginal = document.createElement("a");
					cvOriginal.id = "cv-original";
					cvOriginal.target = "_blank";
					cvOriginal.innerHTML = "Download Original";
					cvOriginal.style.display = 'none';
				}
				
			if(this.dataset.original) {
				cvContent.appendChild(cvOriginal);
			}
			
			modelBox.appendChild(cvOverlay);
			modelBox.appendChild(cvContent);
			modelBox.appendChild(cvNext);
			modelBox.appendChild(cvPrev);
		
			document.body.appendChild(modelBox);
		}
		// Prepare Prev & Next
		cvPrev.removeAttribute("style");
		cvNext.removeAttribute("style");
		galleryId = this.parentNode.parentNode.parentNode;
		parent = this.parentNode.parentNode.children;
		currentIndex = Array.prototype.indexOf.call(parent, this.parentNode);
		
		prev = currentIndex - 1;
		next = currentIndex + 1;
		if(prev >= 0) {
			// Can't use addEventListener here!
			cvPrev.onclick = function(event) {
				model.call(parent[prev].children[0], event);
			};
		} else {
			if(galleryId.querySelector(".pigs_left") != null) {
				// Load prev page
				if(galleryId.querySelector(".pigs_left").children[0] !== 'undefined' && galleryId.querySelector(".pigs_left").children[0].nodeName !== 'SPAN') {
					cvPrev.onclick = function(event) {
						loadPage(galleryId.querySelector(".pigs_left").children[0].href, galleryId.id, "prev", event);
					};
				} else {
					cvPrev.style.display = 'none';
				}
			} else {
				cvPrev.style.display = 'none';
			}
		}
		if(next <= parent.length - 1) {
			// Can't use addEventListener here!
			cvNext.onclick = function(event) {
				model.call(parent[next].children[0], event);
			};
		} else {
			if(galleryId.querySelector(".pigs_right") != null) {
				// Load next page
				if(galleryId.querySelector(".pigs_right").children[0].nodeName === 'A' && next === parent.length) {
					cvNext.onclick = function(event) {
						loadPage(galleryId.querySelector(".pigs_right").children[0].href, galleryId.id, "next", event);
					};
				} else {
					cvNext.style.display = 'none';
				}
			} else {
				cvNext.style.display = 'none';
			}
		}
		
		// Clear model Styles
		cvContent.removeAttribute("style");
		cvInner.removeAttribute("style");
		cvImage.removeAttribute("style");
		
		// Add original if we can
		if(this.dataset.original) {
			cvOriginal.href = this.dataset.original;
			cvOriginal.style.display = 'none';
		}
		
		// Append Image
		cvImage.src       = 'pigs/loading.gif';
		cvImage.className = 'loading';
		loadingImage = new Image(); 
		loadingImage.src = href;
		
		loadingImage.onload = function() {
			cvImage.style = '';
			cvImage.className = '';
		
			imgW = this.width; 
			imgH = this.height;
			
			ratio = imgW / imgH;
			
			if(imgW > winW) {
				imgW = winW - 150;
				imgH = imgW / ratio;
			}
			if(imgH > winH) {
				imgH = winH - 100;
				imgW = imgH * ratio;
			}
			
			imgMW = imgW / 2;
			imgMH = imgH / 2;
			
			
			cvContent.style.marginLeft = "-" + imgMW + "px";
			cvContent.style.marginTop = "-" + imgMH + "px";
			cvContent.style.width = imgW + "px";
			cvContent.style.height = imgH + "px";
			
			cvInner.style.width = imgW + "px";
			cvInner.style.height = imgH + "px";
			
			cvImage.style.width = imgW + "px";
			cvImage.style.height = imgH + "px";
			
			if(cvOriginal) {
				cvOriginal.style.display = '';
			}
			cvImage.src = href;
		};
		
		// Leave a method to close the model
		cvOverlay.addEventListener('click', function() {
			modelBox.style.display = 'none';
			cvContent.removeAttribute("style");
			cvInner.removeAttribute("style");
			cvImage.removeAttribute("style");
			if(this.dataset.original) {
				cvOriginal.href = "#";
				cvOriginal.style.display = 'none';
			}
			cvImage.src     = 'pigs/loading.gif';
		});
		cvInner.addEventListener('click', function() {
			modelBox.style.display = 'none';
			cvContent.removeAttribute("style");
			cvInner.removeAttribute("style");
			cvImage.removeAttribute("style");
			if(this.dataset.original !=='undefined') {
				cvOriginal.href = "#";
				cvOriginal.style.display = 'none';
			}
			cvImage.src     = 'pigs/loading.gif';
		});
		
		// Allow Keys
		document.onkeydown = function(event) {
			var key = event.keyCode || event.which;
			switch (key) {
				case 27: // Escape
					event.preventDefault();
					modelBox.style.display = 'none';
					cvContent.removeAttribute("style");
					cvInner.removeAttribute("style");
					cvImage.removeAttribute("style");
					if(this.dataset.original) {
						cvOriginal.href = "#";
						cvOriginal.style.display = 'none';
					}
					cvImage.src     = 'pigs/loading.gif';
					break;
				// prev
				case 37: // Left Arrow
					if(prev >= 0) {
						// Can't use addEventListener here!
						model.call(parent[prev].children[0], event);
					}
					if(prev >= 0) {
						// Can't use addEventListener here!
						model.call(parent[prev].children[0], event);
					} else {
						if(galleryId.querySelector(".pigs_left") != null) {
							// Load prev page
							if(galleryId.querySelector(".pigs_left").children[0] !== 'undefined' && galleryId.querySelector(".pigs_left").children[0].nodeName !== 'SPAN') {
								loadPage(galleryId.querySelector(".pigs_left").children[0].href, galleryId.id, "prev", event);
							} else {
								cvPrev.style.display = 'none';
							}
						} else {
							cvPrev.style.display = 'none';
						}
					}
					break;
				// next
				case 39: // Right Arrow
					if(next <= parent.length - 1) {
						// Can't use addEventListener here!
						model.call(parent[next].children[0], event);
					} else {
						if(galleryId.querySelector(".pigs_right") != null) {
							// Load next page
							if(galleryId.querySelector(".pigs_right").children[0].nodeName === 'A' && next === parent.length) {
								loadPage(galleryId.querySelector(".pigs_right").children[0].href, galleryId.id, "next", event);
							} else {
								cvNext.style.display = 'none';
							}
						} else {
							cvNext.style.display = 'none';
						}
					}
					break;
			}
		};
	}
	
	function loadPage(url, element, direction, event) {
		var xmlhttp,
			parser,
			xmlDoc,
			gallery,
			images,
			total;
		// Create XMLHttpRequest Object
		if(window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		// Open path
		xmlhttp.open("GET", url, true);
		// Send any data if needed
		xmlhttp.send();
		// Check if we are ready to return the contents
		xmlhttp.onreadystatechange = function() {
			// If request finished and response is ready
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				// Return content of the page
				if(window.DOMParser) {
					parser = new DOMParser();
				} else {
					parser = new ActiveXObject("Microsoft.XMLDOM");
				}
				
				// Use text/html since all text might not be well formatted
				xmlDoc = parser.parseFromString(xmlhttp.responseText, "text/html");
				
				gallery = xmlDoc.querySelector('#' + element);
				
				document.getElementById(element).innerHTML = gallery.innerHTML;
				
				images = document.getElementById(element).querySelectorAll(imagesPath);
				
				total = images.length - 1; // We want based of 0
				
				// Redo image find
				findImages();
				
				if(direction == 'next') {
					model.call(images[0], event);
				} else if(direction == 'prev') {
					model.call(images[total], event);
				} else {
					console.warn("Wrong direction passed!");
				}
			}
		};
	}
	
	// Start finding images
	findImages();
}