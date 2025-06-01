<?php if (isset($_GET['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '<?php echo htmlspecialchars($_GET['error']); ?>',
            background: '#fff',
        });
    </script>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo htmlspecialchars($_GET['success']); ?>',
            background: '#fff',
        });
    </script>
<?php endif; ?>