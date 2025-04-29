<?php
// Start session first before including any files
session_start();

// Include header
include_once('../includes/header.php');

// Include database configuration
require_once('../includes/config.php');

// Fetch active testimonials
$testimonials_query = "SELECT t.*, u.full_name, u.profile_image 
                      FROM testimonials t 
                      JOIN users u ON t.user_id = u.id 
                      WHERE t.status = 'approved' 
                      ORDER BY t.created_at DESC 
                      LIMIT 6";
$testimonials_result = mysqli_query($conn, $testimonials_query);

// Fetch active gallery items
$gallery_query = "SELECT * FROM gallery WHERE status = 'active' ORDER BY created_at DESC LIMIT 9";
$gallery_result = mysqli_query($conn, $gallery_query);

// Fetch active service categories
$categories_query = "SELECT sc.*, COUNT(s.id) as service_count 
                   FROM service_categories sc 
                   LEFT JOIN services s ON sc.id = s.category_id AND s.status = 'active'
                   WHERE sc.status = 'active' 
                   GROUP BY sc.id 
                   ORDER BY sc.name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!-- Loading Animation -->
<div class="loading-screen">
    <div class="loading-content">
        <div class="loading-logo">
            <span class="logo-text">Luxe & Co.Events</span>
            <div class="logo-dot"></div>
        </div>
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
    </div>
</div>

<!-- Hero Section -->
<section class="hero" style='background-image: url("https://scontent.fcmb1-2.fna.fbcdn.net/v/t39.30808-6/476235814_1160440062106885_4567546642157712743_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=f727a1&_nc_ohc=AJ4aUrEewigQ7kNvwGzGp6c&_nc_oc=AdnrjV76Qcmb-4PKASkcREkV9gzcMiD6i3ezNUUGZnr9QrusGCxYsYbURmYmjKG_Bks&_nc_zt=23&_nc_ht=scontent.fcmb1-2.fna&_nc_gid=Vfx17-qgTOJ1HX1j1ENMZw&oh=00_AfEd4GmX5xnayVzk9JEOEumtHQzXbiVx5e3lrwGzaeBB5Q&oe=680EC178");'>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title animate-on-scroll">Your Dream Wedding Starts Here</h1>
            <p class="hero-subtitle animate-on-scroll">Let us create the perfect day that reflects your love story</p>
            <div class="d-flex justify-content-center gap-3 animate-on-scroll">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bridalInfoModal">Start Planning Your Wedding</a>
                <a href="#services" class="btn btn-outline-light">Explore Services</a>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://scontent.fcmb1-2.fna.fbcdn.net/v/t39.30808-6/481700084_1180115956805962_3947798038514654053_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=f727a1&_nc_ohc=Q_FDX9Y2Uk4Q7kNvwEvqCe9&_nc_oc=AdmsRSkPSHuEamh3OqXiQalsioecoR1JgT82dvgxFzbXAOmS8HSQ-lwcJy9G1o6H16M&_nc_zt=23&_nc_ht=scontent.fcmb1-2.fna&_nc_gid=1buD5ioz2qWdMoyznHuFJQ&oh=00_AfGP8_Q-Zg-6gvx6yk66SWCAtlq7NewSQLq27BZ3DildHQ&oe=680EB0AF" alt="About Luxe & Co. Events" class="img-fluid rounded shadow animate-on-scroll">
            </div>
            <div class="col-lg-6">
                <h2 class="about-title animate-on-scroll">Welcome to <span class="text-pink">Luxe & Co. Events</span></h2>
                <p class="about-subtitle animate-on-scroll">Creating Unforgettable Wedding Experiences</p>
                <p class="animate-on-scroll">At Luxe & Co. Events, we believe that your wedding day should be as unique as your love story. With our passion for perfection and attention to detail, we transform your vision into a seamless celebration that reflects your personal style and love.</p>
                <p class="animate-on-scroll">Our team of experienced wedding planners works tirelessly to ensure that every aspect of your special day is meticulously planned and flawlessly executed, allowing you to relax and enjoy every moment.</p>
                <div class="mt-4 animate-on-scroll">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bridalInfoModal">Let's Plan Your Wedding</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="services-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title animate-on-scroll">Our <span class="text-pink">Services</span></h2>
            <p class="animate-on-scroll">We offer a comprehensive range of wedding planning services to make your special day perfect</p>
        </div>
        
        <div class="row">
            <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                    <div class="col-md-6 col-lg-4 mb-4 animate-on-scroll">
                        <div class="service-card text-center">
                            <div class="service-image-container mb-3">
                                <?php 
                                $image_path = !empty($category['image']) ? 
                                    (strpos($category['image'], 'http') === 0 ? $category['image'] : '../' . $category['image']) : 
                                    'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3';
                                ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     class="img-fluid rounded">
                            </div>
                            <div class="service-icon mb-3">
                                <?php
                                // Map category names to Font Awesome icons
                                $icon_map = [
                                    'Hotel Selection' => 'fa-hotel',
                                    'Luxury Catering' => 'fa-utensils',
                                    'Theme Conceptualization' => 'fa-paint-brush',
                                    'Luxury Vehicles' => 'fa-car',
                                    'Beauty & Salon' => 'fa-spa',
                                    'Entertainment' => 'fa-music',
                                    'Photography' => 'fa-camera',
                                    'Invitations' => 'fa-envelope',
                                    'Bridal Styling' => 'fa-tshirt'
                                ];
                                $icon = isset($icon_map[$category['name']]) ? $icon_map[$category['name']] : 'fa-star';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <h3 class="service-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="service-description"><?php echo htmlspecialchars($category['description']); ?></p>
                            <div class="service-price mb-3">
                                <span class="price-tag"><?php echo $category['service_count']; ?> Services</span>
                            </div>
                            <a href="category-services.php?id=<?php echo $category['id']; ?>" class="btn btn-outline-primary">View Services</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">No service categories available at the moment.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="testimonials-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title animate-on-scroll">What Our <span class="text-pink">Couples Say</span></h2>
            <p class="animate-on-scroll">Read about the experiences of our happy couples</p>
        </div>
        
        <div class="row testimonials-grid">
            <?php if ($testimonials_result && mysqli_num_rows($testimonials_result) > 0): ?>
                <?php while ($testimonial = mysqli_fetch_assoc($testimonials_result)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card h-100">
                            <div class="testimonial-header">
                                <?php 
                                $profile_image = '';
                                if (!empty($testimonial['profile_image'])) {
                                    $profile_image = strpos($testimonial['profile_image'], 'http') === 0 ? 
                                        $testimonial['profile_image'] : 
                                        '../uploads/profile_images/' . $testimonial['profile_image'];
                                } else {
                                    // Use a more professional default avatar
                                    $gender = rand(0, 1) ? 'men' : 'women';
                                    $profile_image = "https://randomuser.me/api/portraits/{$gender}/" . rand(1, 99) . ".jpg";
                                }
                                ?>
                                <div class="avatar-wrapper">
                                    <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['full_name']); ?>" 
                                         class="profile-avatar"
                                         onerror="this.src='../assets/images/default-avatar.png'">
                                </div>
                                <div class="testimonial-info">
                                    <h5 class="testimonial-author mb-0"><?php echo htmlspecialchars($testimonial['full_name']); ?></h5>
                                    <small class="testimonial-date text-muted"><?php echo date('F Y', strtotime($testimonial['created_at'])); ?></small>
                                    <div class="testimonial-rating my-2">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $testimonial['rating']) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left text-pink opacity-50 mb-2"></i>
                                <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">No testimonials available at the moment.</div>
            <?php endif; ?>
        </div>

        <style>
/* Modern Testimonial Styles */
.testimonial-card {
    background: #ffffff;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.06);
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.testimonial-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
}

.testimonial-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.avatar-wrapper {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e91e63;
    box-shadow: 0 5px 15px rgba(233, 30, 99, 0.15);
    background: #fff;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.profile-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease-in-out;
}

.avatar-wrapper:hover .profile-avatar {
    transform: scale(1.08);
}

.testimonial-info {
    flex-grow: 1;
}

.testimonial-author {
    font-size: 1.125rem;
    font-weight: 600;
    color: #222;
}

.testimonial-date {
    font-size: 0.85rem;
    color: #888;
    margin-top: 2px;
}

.testimonial-rating {
    font-size: 1rem;
    color: #fbc02d;
    margin-top: 5px;
}

.testimonial-content {
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    padding-top: 1rem;
    position: relative;
}

.testimonial-text {
    font-size: clamp(0.95rem, 2.5vw, 1rem);
    color: #555;
    line-height: 1.6;
    font-style: italic;
    margin: 0;
    position: relative;
    padding-left: 1.5rem;
}

.testimonial-text::before {
    content: "\f10d";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: #e91e63;
    opacity: 0.2;
    font-size: 1.5rem;
    position: absolute;
    left: 0;
    top: -4px;
}

@media (max-width: 768px) {
    .avatar-wrapper {
        width: 3.5rem;
        height: 3.5rem;
    }

    .testimonial-author {
        font-size: 1rem;
    }

    .testimonial-text {
        font-size: 0.9rem;
    }
}
</style>


        <!-- Add Testimonial Form -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="text-center mb-4">Share Your Experience</h4>
                            
                            <?php if (isset($_SESSION['testimonial_success'])): ?>
                                <div class="alert alert-success">
                                    <?php 
                                    echo $_SESSION['testimonial_success'];
                                    unset($_SESSION['testimonial_success']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['testimonial_error'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                    echo $_SESSION['testimonial_error'];
                                    unset($_SESSION['testimonial_error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form action="process_testimonial.php" method="POST">
                                <div class="mb-3">
                                    <label for="testimonial_content" class="form-label">Your Testimonial</label>
                                    <textarea class="form-control" id="testimonial_content" name="content" rows="4" required 
                                              placeholder="Share your wedding experience with us..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating</label>
                                    <div class="star-rating">
                                        <div class="star-input">
                                            <?php for($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                                <i class="fas fa-star"></i>
                                            </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Submit Testimonial</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mt-4">
                <p>Want to share your experience? <a href="login.php">Login</a> to submit a testimonial.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="gallery-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title animate-on-scroll">Our <span class="text-pink">Gallery</span></h2>
            <p class="animate-on-scroll">Browse through our collection of beautiful wedding moments</p>
        </div>
        
        <div class="row g-4">
            <?php if ($gallery_result && mysqli_num_rows($gallery_result) > 0): ?>
                <?php while ($gallery_item = mysqli_fetch_assoc($gallery_result)): ?>
                    <div class="col-md-4 animate-on-scroll">
                        <div class="gallery-item">
                            <img src="../<?php echo htmlspecialchars($gallery_item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($gallery_item['title']); ?>" 
                                 class="img-fluid rounded">
                            <div class="gallery-overlay">
                                <h5><?php echo htmlspecialchars($gallery_item['title']); ?></h5>
                                <?php if ($gallery_item['description']): ?>
                                    <p><?php echo htmlspecialchars($gallery_item['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">No gallery items available at the moment.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Back to Top Button -->
<button id="backToTopBtn" class="back-to-top-btn">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Add JavaScript for testimonial carousel -->
<script>
// Testimonial Carousel
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.testimonial-carousel');
    if (carousel) {
        let isDown = false;
        let startX;
        let scrollLeft;

        carousel.addEventListener('mousedown', (e) => {
            isDown = true;
            carousel.style.cursor = 'grabbing';
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });

        carousel.addEventListener('mouseleave', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });

        carousel.addEventListener('mouseup', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });

        carousel.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });
    }
});
</script>

<?php
// Include footer
include_once('../includes/footer.php');
?>

<style>
/* General Section Padding */
.gallery-section,
.testimonials-section {
    padding: clamp(3rem, 8vw, 5rem) 0;
    background-color: #f9fafb;
}

/* GALLERY STYLES */
.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.gallery-item img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    color: white;
    padding: 1.25rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    transform: translateY(0);
}

.gallery-overlay h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.gallery-overlay p {
    margin-top: 5px;
    font-size: 0.9rem;
    opacity: 0.9;
}

/* TESTIMONIAL CAROUSEL */
.testimonial-carousel {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    gap: 1.5rem;
    padding: 1.5rem 0;
    scrollbar-width: none;
}

.testimonial-carousel::-webkit-scrollbar {
    display: none;
}

.testimonial-card {
    flex: 0 0 300px;
    scroll-snap-align: start;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}

.testimonial-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.testimonial-avatar {
    width: 60px;
    height: 60px;
    margin-right: 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #ff4081;
    flex-shrink: 0;
}

.testimonial-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.testimonial-author {
    font-size: 1.1rem;
    font-weight: 600;
    color: #222;
}

.testimonial-date {
    font-size: 0.85rem;
    color: #999;
}

.testimonial-text {
    font-style: italic;
    color: #555;
    line-height: 1.6;
    font-size: 0.95rem;
}

.testimonial-role {
    font-size: 0.9rem;
    color: #777;
}

/* STAR RATING */
.star-rating {
    display: flex;
    justify-content: center;
    margin: 10px 0;
}

.star-input {
    display: flex;
    flex-direction: row-reverse;
    gap: 5px;
}

.star-input input[type="radio"] {
    display: none;
}

.star-input label {
    cursor: pointer;
    color: #ddd;
    font-size: 1.5rem;
    transition: color 0.2s ease;
}

.star-input label:hover,
.star-input label:hover ~ label,
.star-input input[type="radio"]:checked ~ label {
    color: #ffc107;
}

/* SERVICES */
.service-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}

.service-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.service-image-container img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.service-card:hover .service-image-container img {
    transform: scale(1.05);
}

.service-icon {
    width: 60px;
    height: 60px;
    background: #fff5f8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.service-icon i {
    font-size: 24px;
    color: #ff4081;
}

.service-title {
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;
    color: #333;
    margin: 15px 0 10px;
}

.service-description {
    font-size: 0.9rem;
    color: #666;
    text-align: center;
    margin-bottom: 1rem;
}

.price-tag {
    font-size: 1.2rem;
    font-weight: 600;
    color: #ff4081;
    text-align: center;
}

.btn-outline-primary {
    border: 2px solid #ff4081;
    color: #ff4081;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: inline-block;
    text-align: center;
    text-decoration: none;
}

.btn-outline-primary:hover {
    background-color: #ff4081;
    color: #fff;
}

/* RESPONSIVENESS */
@media (max-width: 768px) {
    .testimonial-card,
    .service-card {
        margin-bottom: 20px;
    }

    .testimonial-avatar {
        width: 50px;
        height: 50px;
    }

    .testimonial-author {
        font-size: 1rem;
    }

    .gallery-item img {
        height: 220px;
    }

    .service-image-container img {
        height: 180px;
    }
}
</style>
<script>
// Back to Top Button
const backToTopBtn = document.getElementById('backToTopBtn');
backToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
window.addEventListener('scroll', () => {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        backToTopBtn.style.display = 'block';
    } else {
        backToTopBtn.style.display = 'none';
    }
});
</script>
