    </div> <!-- /Main Content Column -->
  </div> <!-- /row -->
</div> <!-- /container-fluid -->

<!-- Admin Footer -->
<footer class="text-center py-4 text-muted small border-top bg-white mt-5" style="border-color: var(--somethic-border) !important;">
  <div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-4">
      <div class="mb-2 mb-sm-0">
        &copy; <?php echo date('Y'); ?> <strong>SOMETHIC</strong> Handbag Retail Store — Staff Administration Panel
      </div>
      <div>
        <span class="badge badge-status-completed me-2">System Active</span>
        <span class="text-muted">Role: <?php echo htmlspecialchars($_SESSION['staff_role'] ?? 'Staff'); ?></span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
// Iframe Session Resilience: Ensure session ID is preserved on links and forms
(function() {
  const urlParams = new URLSearchParams(window.location.search);
  let currentSid = urlParams.get('sid') || '<?php echo htmlspecialchars(session_id()); ?>';
  if (currentSid) {
    try { sessionStorage.setItem('somethic_sid', currentSid); } catch(e) {}
  } else {
    try { currentSid = sessionStorage.getItem('somethic_sid'); } catch(e) {}
  }
  
  if (currentSid) {
    // Append sid to all relative admin navigation links if missing
    document.querySelectorAll('a[href]').forEach(function(a) {
      const href = a.getAttribute('href');
      if (href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
        if (!href.includes('sid=')) {
          const sep = href.includes('?') ? '&' : '?';
          a.setAttribute('href', href + sep + 'sid=' + encodeURIComponent(currentSid));
        }
      }
    });
    // Append hidden sid to all forms if missing
    document.querySelectorAll('form').forEach(function(form) {
      if (!form.querySelector('input[name="sid"]')) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'sid';
        input.value = currentSid;
        form.appendChild(input);
      }
    });
  }
})();
</script>
</body>
</html>
