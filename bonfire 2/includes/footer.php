</div> <!-- fecha page-container -->
<footer>
  <p>&copy; 2025 Bonfire. Todos os direitos reservados.</p>
</footer>
<script>
  const toggle = document.getElementById('lightMode');
  toggle.addEventListener('change', () => {
    if (toggle.checked) {
      document.documentElement.style.setProperty('--bg-color', '#E0FBFC');
      document.documentElement.style.setProperty('--text-color', '#001B2E');
    } else {
      document.documentElement.style.setProperty('--bg-color', '#001B2E');
      document.documentElement.style.setProperty('--text-color', '#E0FBFC');
    }
  });
</script>
</body>
</html>



