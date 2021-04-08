var btnAbrirPopupVer = document.getElementById('btn-abrir-popup-ver-1'),
	overlayVer = document.getElementById('overlay-ver-1'),
	popupVer = document.getElementById('popup-ver-1'),
	btnCerrarPopupVer = document.getElementById('btn-cerrar-popup-ver-1');

btnAbrirPopupVer.addEventListener('click', function(){	
	overlayVer.classList.add('active');
	popupVer.classList.add('active');
});

btnCerrarPopupVer.addEventListener('click', function(e){
	e.preventDefault();
	overlayVer.classList.remove('active');
	popupVer.classList.remove('active');
});


var btnAbrirPopupVer2 = document.getElementById('btn-abrir-popup-ver-2'),
overlayVer2 = document.getElementById('overlay-ver-2'),
popupVer2 = document.getElementById('popup-ver-2'),
btnCerrarPopupVer2 = document.getElementById('btn-cerrar-popup-ver-2');

btnAbrirPopupVer2.addEventListener('click', function(){
	overlayVer2.classList.add('active');
	popupVer2.classList.add('active');
});

btnCerrarPopupVer2.addEventListener('click', function(e){
	e.preventDefault();
	overlayVer2.classList.remove('active');
	popupVer2.classList.remove('active');
});

var btnAbrirPopupVer3 = document.getElementById('btn-abrir-popup-ver-3'),
overlayVer3 = document.getElementById('overlay-ver-3'),
popupVer3 = document.getElementById('popup-ver-3'),
btnCerrarPopupVer3 = document.getElementById('btn-cerrar-popup-ver-3');

btnAbrirPopupVer3.addEventListener('click', function(){
	overlayVer3.classList.add('active');
	popupVer3.classList.add('active');
});

btnCerrarPopupVer3.addEventListener('click', function(e){
	e.preventDefault();
	overlayVer3.classList.remove('active');
	popupVer3.classList.remove('active');
});

var btnAbrirPopupVer4 = document.getElementById('btn-abrir-popup-ver-4'),
overlayVer4 = document.getElementById('overlay-ver-4'),
popupVer4 = document.getElementById('popup-ver-4'),
btnCerrarPopupVer4 = document.getElementById('btn-cerrar-popup-ver-4');

btnAbrirPopupVer4.addEventListener('click', function(){
	overlayVer4.classList.add('active');
	popupVer4.classList.add('active');
});

btnCerrarPopupVer4.addEventListener('click', function(e){
	e.preventDefault();
	overlayVer4.classList.remove('active');
	popupVer4.classList.remove('active');
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