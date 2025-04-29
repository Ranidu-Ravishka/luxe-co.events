<?php
// Include database configuration
require_once('../includes/config.php');

// Include header
include_once('../includes/header.php');

// Fetch active service categories with service count
$query = "SELECT sc.*, COUNT(s.id) as service_count 
          FROM service_categories sc 
          LEFT JOIN services s ON sc.id = s.category_id AND s.status = 'active'
          WHERE sc.status = 'active' 
          GROUP BY sc.id 
          ORDER BY sc.name ASC";
$result = mysqli_query($conn, $query);

// Check for query execution error
if (!$result) {
    die("Error fetching categories: " . mysqli_error($conn));
}
?>

<!-- Modern Loading Animation -->
<div class="preloader">
    <div class="preloader-inner">
        <div class="preloader-logo">
            <svg class="preloader-spinner" viewBox="0 0 50 50">
                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
            </svg>
            <span class="brand-name">Luxe & Co.Events</span>
        </div>
    </div>
</div>

<!-- Hero Section with Parallax Effect -->
<section class="hero-section">
    <div class="hero-background" style="background-image: url('https://scontent.fcmb1-2.fna.fbcdn.net/v/t39.30808-6/482004699_1180115240139367_8861999244417115034_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=f727a1&_nc_ohc=TADdcOt7D78Q7kNvwFVpM-v&_nc_oc=AdnAGTG_K5Bw8J6OWTq64tpduhtIBycUqADmGk6JruUmfeI9UBriHRnNSYG82hAkJWU&_nc_zt=23&_nc_ht=scontent.fcmb1-2.fna&_nc_gid=wzT619-FGaidjO6wJyYRHw&oh=00_AfGkTUw3lW2S8eHnfxaGFVNOhh8cRV_5R6y6mxeJf3DMOw&oe=680F1053');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <h1 class="hero-title">Our Premium Services</h1>
                <p class="hero-subtitle">Curated excellence for your perfect day - discover our bespoke wedding services</p>
                <div class="scroll-indicator">
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="services-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Service Collections</h2>
            <p class="section-description">Each category is meticulously crafted to bring your wedding vision to life</p>
        </div>
        
        <div class="services-grid">
            <?php while ($category = mysqli_fetch_assoc($result)): ?>
                <div class="service-card">
                    <div class="card-image">
                        <?php 
                        $image_path = !empty($category['image']) ? 
                            (strpos($category['image'], 'http') === 0 ? $category['image'] : '../' . $category['image']) : 
                            'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3';
                        ?>
                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                             loading="lazy">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <div class="card-footer">
                            <span class="service-count"><?php echo $category['service_count']; ?> services</span>
                            <a href="category-services.php?id=<?php echo $category['id']; ?>" 
                               class="card-button">
                               Explore
                               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                   <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                               </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Modern CSS Styles -->
<style>
:root {
    --primary-color: #e8c8c0;
    --secondary-color: #d4a59a;
    --accent-color: #c48b7f;
    --dark-color: #3a3a3a;
    --light-color: #f9f5f3;
    --text-color: #333;
    --text-light: #777;
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 10px 15px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 20px 25px rgba(0, 0, 0, 0.15);
}

/* Preloader Styles */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--light-color);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.5s ease;
}

.preloader-inner {
    text-align: center;
}

.preloader-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.preloader-spinner {
    width: 50px;
    height: 50px;
    margin-bottom: 20px;
    animation: rotate 1.5s linear infinite;
}

.preloader-spinner .path {
    stroke: var(--accent-color);
    stroke-linecap: round;
    animation: dash 1.5s ease-in-out infinite;
}

.brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--dark-color);
    letter-spacing: 2px;
}

@keyframes rotate {
    100% { transform: rotate(360deg); }
}

@keyframes dash {
    0% {
        stroke-dasharray: 1, 150;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -35;
    }
    100% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -124;
    }
}

/* Hero Section */
.hero-section {
    position: relative;
    height: 100vh;
    min-height: 600px;
    overflow: hidden;
    display: flex;
    align-items: center;
    color: white;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 1;
    transform: scale(1);
    transition: transform 10s ease-out;
}

.hero-section:hover .hero-background {
    transform: scale(1.05);
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%);
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 3;
    width: 100%;
}

.hero-text {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    padding: 0 20px;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 1s ease-out 0.5s forwards;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 4rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.25rem;
    font-weight: 300;
    max-width: 600px;
    margin: 0 auto 2rem;
    line-height: 1.6;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.scroll-indicator {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 50px;
    border: 2px solid rgba(255,255,255,0.5);
    border-radius: 15px;
}

.scroll-indicator span {
    position: absolute;
    top: 10px;
    left: 50%;
    width: 6px;
    height: 6px;
    margin-left: -3px;
    background-color: white;
    border-radius: 50%;
    animation: scrollIndicator 2s infinite;
}

@keyframes scrollIndicator {
    0% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(20px); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Services Section */
.services-section {
    padding: 100px 0;
    background-color: var(--light-color);
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--accent-color);
}

.section-description {
    font-family: 'Montserrat', sans-serif;
    color: var(--text-light);
    max-width: 700px;
    margin: 0 auto;
    font-size: 1.1rem;
    line-height: 1.6;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
}

.service-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    opacity: 0;
    transform: translateY(30px);
}

.service-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.service-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-10px);
}

.card-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.service-card:hover .card-image img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.7) 100%);
}

.card-content {
    padding: 25px;
}

.card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--dark-color);
    margin-bottom: 10px;
}

.card-description {
    font-family: 'Montserrat', sans-serif;
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 20px;
    min-height: 60px;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.service-count {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
    color: var(--accent-color);
    font-weight: 600;
}

.card-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    color: var(--accent-color);
    text-decoration: none;
    padding: 8px 20px;
    border: 1px solid var(--accent-color);
    border-radius: 30px;
    transition: var(--transition);
}

.card-button svg {
    transition: var(--transition);
}

.card-button:hover {
    background: var(--accent-color);
    color: white;
}

.card-button:hover svg {
    transform: translateX(3px);
    fill: white;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .hero-title {
        font-size: 3rem;
    }
    
    .services-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .hero-title {
        font-size: 2rem;
    }
}
</style>

<!-- Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hide preloader when page loads
    window.addEventListener('load', function() {
        const preloader = document.querySelector('.preloader');
        preloader.style.opacity = '0';
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 500);
    });
    
    // Scroll animation for service cards
    const cards = document.querySelectorAll('.service-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, index * 100);
            }
        });
    }, { threshold: 0.1 });
    
    cards.forEach(card => {
        observer.observe(card);
    });
    
    // Parallax effect for hero background
    window.addEventListener('scroll', function() {
        const scrollPosition = window.pageYOffset;
        const heroBackground = document.querySelector('.hero-background');
        heroBackground.style.transform = 'scale(' + (1 + scrollPosition * 0.0005) + ')';
    });
});
</script>

<?php
include_once('../includes/footer.php');
?>