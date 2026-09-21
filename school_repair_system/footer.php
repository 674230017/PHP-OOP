</div>
<footer class="footer mt-auto py-3 bg-light border-top text-center no-print text-muted">
    <div class="container">
        <small>&copy; <?= date('Y') ?> ระบบแจ้งซ่อมภายในโรงเรียน | พัฒนาด้วย PHP 8 & MySQL</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['flash_message'])): ?>
<script>
    Swal.fire({
        icon: '<?= $_SESSION['flash_message']['type'] ?>',
        title: '<?= $_SESSION['flash_message']['title'] ?>',
        text: '<?= $_SESSION['flash_message']['text'] ?>',
        confirmButtonColor: '#0d6efd',
        timer: 3000
    });
</script>
<?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

</body>
</html>
