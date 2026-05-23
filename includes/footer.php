    <!-- JavaScript -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'quiz.php'): ?>
    <script src="/assets/js/quiz.js"></script>
    <script src="/assets/js/confetti.js"></script>
    <?php endif; ?>

    <?php if (basename($_SERVER['PHP_SELF']) === 'results.php'): ?>
    <script src="/assets/js/share.js"></script>
    <?php endif; ?>
</body>
</html>
