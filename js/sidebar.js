document.addEventListener("DOMContentLoaded", function(event) {

const showNavbar = (toggleId, navId, bodyId, activeId) =>{
const toggle = document.getElementById(toggleId),
nav = document.getElementById(navId),
bodypd = document.getElementById(bodyId),
active = document.getElementById(activeId)
// Validate that all variables exist
if(toggle && nav && bodypd && active){
toggle.addEventListener('click', ()=>{
// hide navbar
nav.classList.toggle('show-n')
// change icon
//toggle.classList.toggle('bxs-arrow-to-right')
// add padding to body
bodypd.classList.toggle('body-pd')
// toggle active highlight in sidebar
active.classList.toggle('active-hide')
})
}
}

showNavbar('header-toggle','nav-bar','body-pd','active')

/*===== LINK ACTIVE =====
const linkColor = document.querySelectorAll('.nav_link')

function colorLink(){
if(linkColor){
linkColor.forEach(l=> l.classList.remove('active'))
this.classList.add('active')
}
}
linkColor.forEach(l=> l.addEventListener('click', colorLink))
*/

// Your code to run since DOM is loaded and ready
});