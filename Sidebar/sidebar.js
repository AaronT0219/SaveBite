// nav-link onclick, hide offcanvas
const nav_links = document.querySelectorAll(".nav-link");
nav_links.forEach(nav => {
    nav.addEventListener("click", function() {
        const offcanvasEl = document.querySelector(".offcanvas.show");
        const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
        offcanvas.hide(); 
    });
});