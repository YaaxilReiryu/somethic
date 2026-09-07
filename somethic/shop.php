<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'About SOMETHIC — Boutique & Store Information';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
      <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">About SOMETHIC</li>
    </ol>
  </nav>

  <!-- Business Profile Header Banner -->
  <div class="p-4 p-md-5 rounded-4 mb-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #FFFFFF 0%, var(--somethic-peach) 100%); border: 1px solid var(--somethic-border);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3">
          <div class="brand-logo-mark" style="width: 44px; height: 44px; font-size: 1.4rem;">S</div>
          <div>
            <h1 class="h2 mb-0 brand-logo-text" style="letter-spacing: 0.15em;">SOMETHIC</h1>
            <span class="badge badge-somethic-available">Handbag Retail Boutique</span>
          </div>
        </div>
        <p class="lead fw-normal text-dark mb-3" style="font-family: var(--font-serif); font-style: italic;">
          "SOMETHIC is a modern handbag boutique offering stylish and affordable handbags designed for everyday elegance."
        </p>
        <p class="text-muted mb-0" style="line-height: 1.8; max-width: 680px;">
          Established in the heart of Kuala Lumpur, SOMETHIC celebrates the art of everyday accessorizing. We bring together modern architectural silhouettes, durable cruelty-free vegan leathers, and thoughtful interior compartments tailored for confident women on the go.
        </p>
      </div>

      <div class="col-lg-4 text-center">
        <div class="p-3 bg-white rounded-4 shadow-sm border" style="border-color: var(--somethic-border) !important;">
          <img src="assets/images/bag_luna.svg" alt="SOMETHIC Boutique" class="img-fluid rounded-3 mb-2" style="max-height: 180px;">
          <h6 class="fw-bold mb-0">Flagship Boutique</h6>
          <small class="text-muted">Bukit Bintang, Kuala Lumpur</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Content Grid -->
  <div class="row g-5">
    <!-- Left Column: About SOMETHIC & Short Brand Story -->
    <div class="col-lg-7">
      <!-- About SOMETHIC -->
      <div class="mb-5">
        <span class="text-uppercase small fw-bold" style="letter-spacing: 0.90em; color: var(--somethic-dark);">Who We Are</span>
        <h2 class="mt-1 mb-3">About SOMETHIC</h2>
        <p class="text-muted" style="line-height: 1.8;">
          Founded with a passion for refined minimalism, SOMETHIC curates accessories that effortlessly bridge daytime utility and evening glamour. We believe luxury should never be gatekept by exorbitant price tags or fragile materials. Every handbag in our boutique is tested for weight distribution, daily durability, and aesthetic longevity.
        </p>
        <p class="text-muted" style="line-height: 1.8;">
          Whether you are stepping into a lecture hall, presenting in a boardroom, or catching weekend coffee with friends, SOMETHIC handbags provide the polished finishing touch to your personal style.
        </p>
      </div>

      <!-- Short Brand Story -->
      <div class="mb-5 p-4 rounded-4" style="background-color: var(--somethic-sand); border: 1px solid var(--somethic-border);">
        <span class="text-uppercase small fw-bold" style="letter-spacing: 0.12em; color: var(--somethic-gold);">Our Origins</span>
        <h3 class="mt-1 mb-3">Our Brand Story</h3>
        <p class="text-muted mb-3" style="line-height: 1.8;">
          The name <strong>SOMETHIC</strong> was born from a simple belief: that every woman deserves <em>"something aesthetic, something authentic, and something iconic"</em> to carry through life's milestones.
        </p>
        <p class="text-muted mb-3" style="line-height: 1.8;">
          Starting as an intimate trunk showcase in 2021, founder Ariana Sofea recognized a gap in the local market between mass-produced fast-fashion bags that wear out within months and imported luxury labels priced beyond the everyday budget. SOMETHIC bridges this divide by delivering boutique craftsmanship, customized reservation services, and transparent retail hospitality.
        </p>
        <div class="d-flex align-items-center gap-3 pt-2">
          <a href="products.php" class="btn btn-somethic-dark btn-sm">Explore Collection</a>
          <a href="staff.php" class="btn btn-somethic-outline btn-sm">Meet Store Staff</a>
        </div>
      </div>

      <!-- Core Values -->
      <div>
        <h4 class="mb-3">Our Core Commitments</h4>
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="p-3 bg-white rounded-3 border" style="border-color: var(--somethic-border) !important;">
              <i class="bi bi-gem fs-4 text-warning mb-2 d-block"></i>
              <h6 class="fw-bold mb-1">Durable Craftsmanship</h6>
              <p class="small text-muted mb-0">High-grade water-resistant vegan leathers and reinforced metal rivets.</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-white rounded-3 border" style="border-color: var(--somethic-border) !important;">
              <i class="bi bi-tag fs-4 text-warning mb-2 d-block"></i>
              <h6 class="fw-bold mb-1">Affordable Luxury</h6>
              <p class="small text-muted mb-0">Realistic Malaysian Ringgit prices without intermediate wholesale markups.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Store Details, Opening Hours, Location, Socials -->
    <div class="col-lg-5">
      <div class="card-somethic p-4 mb-4">
        <h4 class="mb-3">Store Information</h4>
        
        <!-- Address -->
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom" style="border-color: var(--somethic-border) !important;">
          <div class="brand-logo-mark flex-shrink-0" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-geo-alt"></i></div>
          <div>
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.08em;">Store Address</span>
            <span class="text-dark fw-semibold">123 Fashion Walk, Bukit Bintang, 50200 Kuala Lumpur, Malaysia</span>
            <small class="text-muted d-block mt-1">Ground Floor, Retail Promenade (Near Monorail Bukit Bintang)</small>
          </div>
        </div>

        <!-- Phone -->
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom" style="border-color: var(--somethic-border) !important;">
          <div class="brand-logo-mark flex-shrink-0" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-telephone"></i></div>
          <div>
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.08em;">Phone Number</span>
            <a href="tel:+60388881234" class="text-dark fw-semibold">+60 3-8888 1234</a>
            <small class="text-muted d-block mt-1">Mobile / WhatsApp: +60 12-345 6789</small>
          </div>
        </div>

        <!-- Email -->
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom" style="border-color: var(--somethic-border) !important;">
          <div class="brand-logo-mark flex-shrink-0" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-envelope"></i></div>
          <div>
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.08em;">Customer Email</span>
            <a href="mailto:hello@somethic.com" class="text-dark fw-semibold">hello@somethic.com</a>
            <small class="text-muted d-block mt-1">Reservations & inquiries answered within 4 hours</small>
          </div>
        </div>

        <!-- Opening Hours -->
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom" style="border-color: var(--somethic-border) !important;">
          <div class="brand-logo-mark flex-shrink-0" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-clock"></i></div>
          <div>
            <span class="text-uppercase small text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.08em;">Opening Hours</span>
            <div class="small text-dark mt-1">
              <div><strong>Monday – Friday:</strong> 10:00 AM – 10:00 PM</div>
              <div><strong>Saturday – Sunday:</strong> 10:00 AM – 10:00 PM</div>
              <div><strong>Public Holidays:</strong> 10:00 AM – 8:00 PM</div>
            </div>
          </div>
        </div>

        <!-- Social Media Links -->
        <div>
          <span class="text-uppercase small text-muted fw-bold d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.08em;">Social Media Channels</span>
          <div class="d-flex flex-column gap-2 small">
            <a href="https://instagram.com" target="_blank" rel="noopener" class="d-flex align-items-center text-dark">
              <i class="bi bi-instagram me-2 text-warning"></i>
              <span><strong>Instagram:</strong> @somethic.official</span>
            </a>
            <a href="https://facebook.com" target="_blank" rel="noopener" class="d-flex align-items-center text-dark">
              <i class="bi bi-facebook me-2 text-warning"></i>
              <span><strong>Facebook:</strong> /somethicboutique</span>
            </a>
            <a href="https://tiktok.com" target="_blank" rel="noopener" class="d-flex align-items-center text-dark">
              <i class="bi bi-tiktok me-2 text-warning"></i>
              <span><strong>TikTok:</strong> @somethic_my</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Quick Reservation Callout -->
      <div class="p-4 rounded-4 text-center" style="background-color: var(--somethic-sand); border: 1px dashed var(--somethic-gold);">
        <h5 class="mb-2">Planning a Store Visit?</h5>
        <p class="small text-muted mb-3">Reserve your chosen handbag online before arriving to ensure it is kept in stock for your styling consultation.</p>
        <a href="reservation.php" class="btn btn-sm btn-somethic-gold px-4">Reserve Handbag for Pickup</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
