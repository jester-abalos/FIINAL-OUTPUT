
<nav class="navbar">
    <div class="navbar-logo">
        <a href="#footer"><img src="../../assets/img/LOGO1.png" alt="Logo"></a>
    </div>
    <ul class="navbar-links" id="navbar-links">
        <li><a href="Dashboard.php">Home</a></li>
        <li><a href="Categories.php">Categories</a></li>
        <li><a href="OrdersPage.php">Orders</a></li>
        
        
    </ul>
    <div class="search-container">
        <input type="text" placeholder="Search..." id="search-bar">
        <span id="search-icon"><img src="../../assets/img/search.png" alt="Search"></span>
    </div>
    <div class="username">
        <p><?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="cart-user">
        <a href="addtocar.php"><img src="../../assets/img/cart.png" alt="Cart"></a>
        <span></span>
        <a href="ManageProfile.php"><img src="../../assets/img/user.png" alt="User"></a>
    </div>
    <div class="navbar-toggle" id="navbar-toggle">
        
        <span></span>
        <span></span>
        <span></span>
     
    </div>
    

</nav>

