<?php
session_start();
require_once('../includes/config.php');
include_once('../includes/header.php');

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category details
$category_query = "SELECT * FROM service_categories WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category_result = $stmt->get_result();
$category = $category_result->fetch_assoc();

if (!$category) {
    header("Location: services.php");
    exit();
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

<!-- Hero Section with Parallax -->
<section class="category-hero">
    <div class="hero-background" style="background-image: url('<?php 
        echo !empty($category['image']) ? 
            (strpos($category['image'], 'http') === 0 ? $category['image'] : '../' . $category['image']) : 
            'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3'; 
    ?>');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <h1 class="hero-title animate-on-scroll"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="hero-subtitle animate-on-scroll"><?php echo htmlspecialchars($category['description'] ?? 'Discover our premium ' . $category['name'] . ' services tailored for your special day.'); ?></p>
                <div class="hero-actions animate-on-scroll">
                    <a href="services.php" class="hero-back-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <?php echo htmlspecialchars($category['name']); ?> Services</h2>
            <p class="section-description">Exquisite options tailored for your perfect wedding experience</p>
        </div>
        
        <div class="services-grid">
            <?php
            $services_query = "SELECT * FROM services WHERE category_id = ? AND status = 'active' ORDER BY name ASC";
            $stmt = $conn->prepare($services_query);
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $services_result = $stmt->get_result();

            if ($services_result->num_rows > 0) {
                while ($service = $services_result->fetch_assoc()) {
                    $image_path = !empty($service['image']) ? 
                        (strpos($service['image'], 'http') === 0 ? $service['image'] : '../' . $service['image']) : 
                        'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3';
                    ?>
                    <div class="service-card animate-on-scroll">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                 loading="lazy">
                            <div class="image-overlay"></div>
                            <div class="price-badge">$<?php echo number_format($service['price'], 2); ?></div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p class="card-description"><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="card-actions">
                                <button onclick="showServiceDetails(<?php echo $service['id']; ?>)" 
                                        class="btn-details">
                                    View Details
                                </button>
                                <button onclick="addToCart(<?php echo $service['id']; ?>)" 
                                        class="btn-add-to-cart">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                    </svg>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-services">No services available in this category at the moment.</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="modal-image-container">
                        <img id="modalServiceImage" src="" alt="" class="modal-image">
                    </div>
                    <div class="modal-content-container">
                        <h3 id="modalServiceName" class="modal-title"></h3>
                        <div class="modal-price" id="modalServicePrice"></div>
                        <p id="modalServiceDescription" class="modal-description"></p>
                        <div class="modal-features">
                            <h4>Service Features</h4>
                            <ul id="modalServiceFeatures"></ul>
                        </div>
                        <button id="addToCartBtn" class="modal-add-to-cart">
                            Add to Cart
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

/* Category Hero Section */
.category-hero {
    position: relative;
    height: 70vh;
    min-height: 500px;
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

.category-hero:hover .hero-background {
    transform: scale(1.05);
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%);
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
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 1s ease-out 0.3s forwards;
}

.hero-subtitle {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.25rem;
    font-weight: 300;
    max-width: 600px;
    margin: 0 auto 2rem;
    line-height: 1.6;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 1s ease-out 0.5s forwards;
}

.hero-actions {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 1s ease-out 0.7s forwards;
}

.hero-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 30px;
    transition: var(--transition);
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(5px);
}

.hero-back-btn svg {
    transition: var(--transition);
}

.hero-back-btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.5);
}

.hero-back-btn:hover svg {
    transform: translateX(-3px);
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
    border-radius: 12px;
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

.price-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--accent-color);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

.card-actions {
    display: flex;
    gap: 10px;
}

.btn-details {
    flex: 1;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    color: var(--accent-color);
    text-decoration: none;
    padding: 10px;
    border: 1px solid var(--accent-color);
    border-radius: 6px;
    transition: var(--transition);
    background: transparent;
    cursor: pointer;
}

.btn-details:hover {
    background: rgba(196, 139, 127, 0.1);
}

.btn-add-to-cart {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    color: white;
    text-decoration: none;
    padding: 10px;
    border: 1px solid var(--accent-color);
    border-radius: 6px;
    transition: var(--transition);
    background: var(--accent-color);
    cursor: pointer;
}

.btn-add-to-cart:hover {
    background: var(--secondary-color);
    border-color: var(--secondary-color);
}

.btn-add-to-cart svg {
    transition: var(--transition);
}

.btn-add-to-cart:hover svg {
    transform: scale(1.1);
}

.no-services {
    grid-column: 1 / -1;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    color: var(--text-light);
    padding: 40px 0;
}

/* Service Details Modal */
.modal-content {
    border-radius: 15px;
    border: none;
    overflow: hidden;
}

.btn-close {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1;
    background: rgba(0,0,0,0.2);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: var(--transition);
}

.btn-close:hover {
    opacity: 1;
    background: rgba(0,0,0,0.3);
}

.modal-body {
    padding: 0;
}

.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 500px;
}

.modal-image-container {
    position: relative;
    overflow: hidden;
}

.modal-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
}

.modal-content-container {
    padding: 40px;
    display: flex;
    flex-direction: column;
}

.modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: var(--dark-color);
    margin-bottom: 15px;
}

.modal-price {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--accent-color);
    margin-bottom: 20px;
}

.modal-description {
    font-family: 'Montserrat', sans-serif;
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 30px;
}

.modal-features {
    margin-bottom: 30px;
}

.modal-features h4 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 15px;
}

.modal-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.modal-features li {
    font-family: 'Montserrat', sans-serif;
    color: var(--text-light);
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.modal-features li::before {
    content: '•';
    color: var(--accent-color);
    font-size: 1.2rem;
    line-height: 1;
}

.modal-add-to-cart {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 6px;
    transition: var(--transition);
    background: var(--accent-color);
    cursor: pointer;
    margin-top: auto;
}

.modal-add-to-cart:hover {
    background: var(--secondary-color);
}

.modal-add-to-cart svg {
    transition: var(--transition);
}

.modal-add-to-cart:hover svg {
    transform: scale(1.1);
}

/* Responsive Styles */
@media (max-width: 992px) {
    .hero-title {
        font-size: 3rem;
    }
    
    .services-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
    
    .modal-grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    
    .modal-image-container {
        height: 300px;
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
    
    .category-hero {
        height: 60vh;
        min-height: 400px;
    }
}

@media (max-width: 576px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .card-actions {
        flex-direction: column;
    }
    
    .modal-content-container {
        padding: 25px;
    }
}
</style>

<!-- JavaScript -->
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

let currentServiceId = null;

function showServiceDetails(serviceId) {
    currentServiceId = serviceId;
    
    fetch(`get_service_details.php?id=${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const service = data.service;
                
                // Update modal content
                document.getElementById('modalServiceImage').src = service.image;
                document.getElementById('modalServiceName').textContent = service.name;
                document.getElementById('modalServiceDescription').textContent = service.description;
                document.getElementById('modalServicePrice').textContent = `$${parseFloat(service.price).toFixed(2)}`;
                
                // Update features
                const featuresList = document.getElementById('modalServiceFeatures');
                featuresList.innerHTML = '';
                if (service.features) {
                    service.features.split('\n').forEach(feature => {
                        if (feature.trim()) {
                            featuresList.innerHTML += `<li>${feature.trim()}</li>`;
                        }
                    });
                }
                
                // Update Add to Cart button
                const addToCartBtn = document.getElementById('addToCartBtn');
                addToCartBtn.onclick = function() {
                    addToCart(serviceId);
                };
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('serviceDetailsModal'));
                modal.show();
            } else {
                showToast('Failed to load service details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load service details', 'error');
        });
}


function addToCart(serviceId) {
    console.log('Adding service to cart:', serviceId);
    fetch('../pages/cart.php', {  // Updated path to be relative to the root
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=add&service_id=${serviceId}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Show success message
            alert(data.message);
            // Close modal if it exists
            const modal = document.getElementById('serviceDetailsModal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        } else {
            // Show error message with more details
            alert(data.message || 'Failed to add service to cart');
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        alert('Failed to add service to cart. Please make sure you are logged in.');
    });
}

function showToast(message, type) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>

<?php
include_once('../includes/footer.php');
?>