var btnAbrirPopup = document.getElementById('btn-abrir-popup'),
	overlay = document.getElementById('overlay'),
	popup = document.getElementById('popup'),
	btnCerrarPopup = document.getElementById('btn-cerrar-popup');

btnAbrirPopup.addEventListener('click', function(){
	console.log("valor");
	overlay.classList.add('active');
	popup.classList.add('active');
});

btnCerrarPopup.addEventListener('click', function(e){
	e.preventDefault();
	overlay.classList.remove('active');
	popup.classList.remove('active');
});







var btnAbrirPopupE = document.getElementById('btn-abrir-popup-edith-1'),
	overlayE = document.getElementById('overlay-edith-1'),
	popupE = document.getElementById('popup-edith-1'),
	btnCerrarPopupE = document.getElementById('btn-cerrar-popup-edith-1');

btnAbrirPopupE.addEventListener('click', function(){
	console.log("valor");
	overlayE.classList.add('active');
	popupE.classList.add('active');
});

btnCerrarPopupE.addEventListener('click', function(e){
	e.preventDefault();
	overlayE.classList.remove('active');
	popupE.classList.remove('active');
});

var btnAbrirPopupE2 = document.getElementById('btn-abrir-popup-edith-2'),
	overlayE2 = document.getElementById('overlay-edith-2'),
	popupE2 = document.getElementById('popup-edith-2'),
	btnCerrarPopupE2 = document.getElementById('btn-cerrar-popup-edith-2');

btnAbrirPopupE2.addEventListener('click', function(){
	overlayE2.classList.add('active');
	popupE2.classList.add('active');
});

btnCerrarPopupE2.addEventListener('click', function(e){
	e.preventDefault();
	overlayE2.classList.remove('active');
	popupE2.classList.remove('active');
});

var btnAbrirPopupE3 = document.getElementById('btn-abrir-popup-edith-3'),
	overlayE3 = document.getElementById('overlay-edith-3'),
	popupE3 = document.getElementById('popup-edith-3'),
	btnCerrarPopupE3 = document.getElementById('btn-cerrar-popup-edith-3');

btnAbrirPopupE3.addEventListener('click', function(){	
	overlayE3.classList.add('active');
	popupE3.classList.add('active');
});

btnCerrarPopupE3.addEventListener('click', function(e){
	e.preventDefault();
	overlayE3.classList.remove('active');
	popupE3.classList.remove('active');
});

var btnAbrirPopupE4 = document.getElementById('btn-abrir-popup-edith-4'),
	overlayE4 = document.getElementById('overlay-edith-4'),
	popupE4 = document.getElementById('popup-edith-4'),
	btnCerrarPopupE4 = document.getElementById('btn-cerrar-popup-edith-4');

btnAbrirPopupE4.addEventListener('click', function(){	
	overlayE4.classList.add('active');
	popupE4.classList.add('active');
});

btnCerrarPopupE4.addEventListener('click', function(e){
	e.preventDefault();
	overlayE4.classList.remove('active');
	popupE4.classList.remove('active');
});

var btnAbrirPopupE5 = document.getElementById('btn-abrir-popup-edith-5'),
	overlayE5 = document.getElementById('overlay-edith-5'),
	popupE5 = document.getElementById('popup-edith-5'),
	btnCerrarPopupE5 = document.getElementById('btn-cerrar-popup-edith-5');

btnAbrirPopupE5.addEventListener('click', function(){	
	overlayE5.classList.add('active');
	popupE5.classList.add('active');
});

btnCerrarPopupE5.addEventListener('click', function(e){
	e.preventDefault();
	overlayE5.classList.remove('active');
	popupE5.classList.remove('active');
});
