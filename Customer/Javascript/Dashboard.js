document.getElementById("navbar-toggle").addEventListener("click", function() {
    const navLinks = document.getElementById("navbar-links");
    navLinks.classList.toggle("active");
});
// JavaScript for Search Bar
document.getElementById("search-icon").addEventListener("click", function() {
    const query = document.getElementById("search-bar").value;
    console.log("Search query:", query);
    // Implement search functionality here
});