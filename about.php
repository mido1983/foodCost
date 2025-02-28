<?php
$current_page = 'about';
$page_title = 'About Us';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">About FoodCost Manager</h1>
            
            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <h2 class="h4 mb-3">Our Mission</h2>
                    <p>FoodCost Manager is dedicated to helping restaurants, cafes, and food businesses of all sizes to optimize their food costs, reduce waste, and increase profitability through efficient inventory and recipe management.</p>
                    
                    <h2 class="h4 mb-3 mt-4">Our Story</h2>
                    <p>Founded in 2023, FoodCost Manager was born out of the real challenges faced by food industry professionals. Our founder, with over 15 years of experience in restaurant management, recognized the need for a more intuitive and comprehensive solution for food cost control.</p>
                    
                    <p>What started as a simple spreadsheet evolved into a powerful platform designed specifically for the unique needs of the food service industry. Today, we're proud to serve thousands of businesses around the world, helping them save time, reduce costs, and grow their profits.</p>
                    
                    <h2 class="h4 mb-3 mt-4">Our Values</h2>
                    <div class="row mt-4">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-leaf fa-2x text-success me-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5">Sustainability</h3>
                                    <p>We believe in reducing food waste and promoting sustainable practices in the food industry.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb fa-2x text-warning me-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5">Innovation</h3>
                                    <p>We constantly strive to improve our platform with new features and technologies.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-hands-helping fa-2x text-primary me-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5">Support</h3>
                                    <p>We're committed to providing exceptional customer service and support.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt fa-2x text-danger me-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5">Security</h3>
                                    <p>We prioritize the security and privacy of our users' data.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="h4 mb-3 mt-4">Meet Our Team</h2>
                    <div class="row mt-4">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= SITE_URL ?>/assets/images/team/team-1.jpg" class="card-img-top" alt="Team Member">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Michael Doroshenko</h5>
                                    <p class="card-text text-muted">Founder & CEO</p>
                                    <p class="card-text">With extensive experience in restaurant management, Michael leads our vision for revolutionizing food cost management.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= SITE_URL ?>/assets/images/team/team-2.jpg" class="card-img-top" alt="Team Member">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Sarah Johnson</h5>
                                    <p class="card-text text-muted">CTO</p>
                                    <p class="card-text">Sarah brings technical expertise and innovation to our platform, ensuring we stay at the cutting edge of technology.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= SITE_URL ?>/assets/images/team/team-3.jpg" class="card-img-top" alt="Team Member">
                                <div class="card-body text-center">
                                    <h5 class="card-title">David Kim</h5>
                                    <p class="card-text text-muted">Head of Customer Success</p>
                                    <p class="card-text">David ensures our customers get the most value from FoodCost Manager through training and support.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="h4 mb-4">Contact Us</h2>
                    <p>Have questions or feedback? We'd love to hear from you!</p>
                    <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary">Get in Touch</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 