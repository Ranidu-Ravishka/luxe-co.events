<?php
// Include header
include_once('../includes/header.php');

// Get service parameter from URL
$service = isset($_GET['service']) ? $_GET['service'] : '';

// Define service details
$services = [
    'hotel-selection' => [
        'title' => 'Hotel Selection',
        'icon' => 'fas fa-hotel',
        'emoji' => '🏨',
        'description' => 'Our Hotel Selection service helps you find the perfect accommodation for your wedding guests. We work with a curated list of luxury hotels and venues to ensure your guests have a comfortable and memorable stay.',
        'features' => [
            'Personalized hotel recommendations based on your preferences and budget',
            'Group booking discounts and special wedding rates',
            'Coordination of room blocks for wedding guests',
            'Assistance with transportation between venues',
            'Welcome gift arrangements for out-of-town guests'
        ],
        'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'luxury-catering' => [
        'title' => 'Luxury Catering',
        'icon' => 'fas fa-utensils',
        'emoji' => '🍽',
        'description' => 'Our Luxury Catering service brings culinary excellence to your wedding celebration. We partner with top chefs and catering companies to create a customized menu that reflects your taste and preferences.',
        'features' => [
            'Customized menu planning with professional chefs',
            'Menu tasting sessions before your event',
            'Dietary accommodation for special requirements (vegetarian, vegan, gluten-free, etc.)',
            'Premium bar service and signature cocktail creation',
            'Elegant table settings and service staff'
        ],
        'image' => 'https://images.unsplash.com/photo-1565538810643-b5bdb714032a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'theme-conceptualization' => [
        'title' => 'Theme Conceptualization',
        'icon' => 'fas fa-paint-brush',
        'emoji' => '🎨',
        'description' => 'Our Theme Conceptualization service helps bring your wedding vision to life. From elegant and classic to modern and unique, we design a cohesive theme that reflects your personality and style.',
        'features' => [
            'Personalized theme development based on your preferences',
            'Color palette selection and coordination',
            'Custom decor elements and installations',
            'Lighting design to enhance the ambiance',
            'Floral arrangements and centerpieces that complement your theme'
        ],
        'image' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'luxury-vehicles' => [
        'title' => 'Luxury Vehicles',
        'icon' => 'fas fa-car',
        'emoji' => '🚗',
        'description' => 'Make a grand entrance and exit with our Luxury Vehicles service. We offer a selection of premium vehicles to add elegance and style to your wedding transportation.',
        'features' => [
            'Wide selection of luxury and vintage vehicles',
            'Chauffeur service for the wedding day',
            'Vehicle decoration to match your wedding theme',
            'Coordination of transportation schedule',
            'Guest shuttle services between venues'
        ],
        'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'beauty-salon' => [
        'title' => 'Beauty & Salon Services',
        'icon' => 'fas fa-spa',
        'emoji' => '💄',
        'description' => 'Look your absolute best on your wedding day with our Beauty & Salon Services. Our team of professional stylists and makeup artists will ensure you radiate confidence and beauty.',
        'features' => [
            'Bridal makeup and hair styling',
            'Groom grooming services',
            'Pre-wedding skincare consultations',
            'Trial sessions before the wedding day',
            'On-site services for the bridal party'
        ],
        'image' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'entertainment' => [
        'title' => 'Entertainment',
        'icon' => 'fas fa-music',
        'emoji' => '🎵',
        'description' => 'Keep your guests entertained throughout your wedding celebration with our Entertainment services. From live music to interactive performances, we curate entertainment that matches your style.',
        'features' => [
            'Live bands and musicians for ceremonies and receptions',
            'Professional DJs with customized playlists',
            'Dance performances and cultural entertainment',
            'Interactive activities for guests',
            'Sound and lighting equipment setup'
        ],
        'image' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'photography' => [
        'title' => 'Photography & Cinematography',
        'icon' => 'fas fa-camera',
        'emoji' => '📸',
        'description' => 'Capture every precious moment of your wedding day with our Photography & Cinematography services. Our professional photographers and videographers create timeless memories you\'ll cherish forever.',
        'features' => [
            'Pre-wedding photoshoots and engagement sessions',
            'Full-day wedding photography coverage',
            'Cinematic wedding films and highlight reels',
            'Drone photography and videography',
            'Photo albums and digital galleries'
        ],
        'image' => 'https://images.unsplash.com/photo-1532712938310-34cb3982ef74?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'invitations' => [
        'title' => 'Customized Invitations',
        'icon' => 'fas fa-envelope',
        'emoji' => '✉️',
        'description' => 'Make a lasting first impression with our Customized Invitations service. We design elegant and personalized wedding stationery that sets the tone for your special day.',
        'features' => [
            'Custom invitation design that matches your wedding theme',
            'Save-the-date cards and RSVP management',
            'Wedding programs and menu cards',
            'Thank-you cards and wedding announcements',
            'Digital and printed invitation options'
        ],
        'image' => 'https://images.unsplash.com/photo-1607344645866-009c320b63e0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ],
    'bridal-styling' => [
        'title' => 'Bridal Styling & Grooming',
        'icon' => 'fas fa-female',
        'emoji' => '👰',
        'description' => 'Our Bridal Styling & Grooming service helps you look and feel your best on your wedding day. From selecting the perfect wedding attire to accessories and jewelry, we ensure you shine on your special day.',
        'features' => [
            'Personalized styling consultations',
            'Wedding dress and suit selection assistance',
            'Accessories and jewelry coordination',
            'Outfit fittings and alterations',
            'Day-of dressing assistance'
        ],
        'image' => 'https://images.unsplash.com/photo-1594472436416-4b5a5f2e0795?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80'
    ]
];

// Check if service exists
if (!empty($service) && isset($services[$service])) {
    $serviceDetails = $services[$service];
} else {
    // Redirect to services page if service not found
    header('Location: services.php');
    exit;
}
?>

<!-- Hero Section -->
<section class="hero" style="background-image: url('<?php echo $serviceDetails['image']; ?>');">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title animate-on-scroll"><?php echo $serviceDetails['title']; ?> <?php echo $serviceDetails['emoji']; ?></h1>
            <p class="hero-subtitle animate-on-scroll"><?php echo $serviceDetails['description']; ?></p>
            <div class="d-flex justify-content-center gap-3 animate-on-scroll">
                <?php if ($service == 'bridal-styling' || $service == 'bridal-wear' || $service == 'groom-wear'): ?>
                    <a href="services pages/bridel-groom.php" class="btn btn-primary">Book This Service</a>
                <?php else: ?>
                    <a href="services pages/<?php echo $service; ?>.php" class="btn btn-primary">Book This Service</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Service Details Section -->
<section id="service-details" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <div class="service-icon d-inline-block mb-3">
                                <i class="<?php echo $serviceDetails['icon']; ?> fa-3x text-pink"></i>
                            </div>
                            <h2 class="h1 mb-4"><?php echo $serviceDetails['title']; ?> <?php echo $serviceDetails['emoji']; ?></h2>
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <p class="lead mb-4"><?php echo $serviceDetails['description']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <h3 class="h4 mb-4">What's Included</h3>
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($serviceDetails['features'] as $feature): ?>
                                        <li class="list-group-item bg-transparent px-0 py-3 d-flex align-items-center">
                                            <span class="badge bg-pink rounded-circle me-3"><i class="fas fa-check"></i></span>
                                            <?php echo $feature; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <!-- Removed the Book This Service button since we're removing the contact section -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Services Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Other Services You Might Like</h2>
            <p>Explore more of our premium wedding services</p>
        </div>
        
        <div class="row">
            <?php
            // Get 3 random services excluding the current one
            $relatedServices = array_diff_key($services, [$service => '']);
            $relatedServices = array_rand($relatedServices, min(3, count($relatedServices)));
            
            if (!is_array($relatedServices)) {
                $relatedServices = [$relatedServices];
            }
            
            foreach ($relatedServices as $relatedServiceKey):
                $relatedService = $services[$relatedServiceKey];
            ?>
            <div class="col-md-4 mb-4">
                <div class="service-card h-100">
                    <div class="service-image-container">
                        <img src="<?php echo $relatedService['image']; ?>" alt="<?php echo $relatedService['title']; ?>" class="img-fluid rounded-top" style="height: 200px; object-fit: cover; width: 100%;">
                    </div>
                    <div class="text-center p-4">
                        <div class="service-icon">
                            <i class="<?php echo $relatedService['icon']; ?>"></i>
                        </div>
                        <h3 class="service-title"><?php echo $relatedService['title']; ?> <?php echo $relatedService['emoji']; ?></h3>
                        <p><?php echo substr($relatedService['description'], 0, 100); ?>...</p>
                        <a href="service-details.php?service=<?php echo $relatedServiceKey; ?>" class="btn btn-outline-primary mt-3">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
// Include footer
include_once('../includes/footer.php');
?> 