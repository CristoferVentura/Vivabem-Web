    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> — VivaBem. Todos os direitos reservados. Projeto de TCC.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script src="<?= e(asset('js/main.js')) ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <script src="<?= e(asset('js/' . $extraJs)) ?>"></script>
    <?php endif; ?>
</body>
</html>
