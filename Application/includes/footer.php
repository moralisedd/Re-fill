<?php
/**
 * Re-fill — Shared HTML footer partial
 */
?>
</main><!-- /#main-content -->

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Re-fill. Helping independent cafes go reusable.</p>
        <nav aria-label="Footer navigation">
            <a href="<?= BASE_URL ?>/about.php">About</a>
            <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>
            <a href="<?= BASE_URL ?>/accessibility.php">Accessibility</a>
        </nav>
    </div>
</footer>

<!-- Bootstrap JS bundle (includes Popper) — loaded at end of body for performance -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>
