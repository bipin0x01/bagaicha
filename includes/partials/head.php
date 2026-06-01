<?php
/**
 * Shared HTML Head Partial
 * Pre-configured with Tailwind CSS v3 CDN, Poppins Google Font, and dynamic page metadata.
 */
$meta_title = isset($page_title) ? $page_title : 'Bagaicha — Premium Bonsai from Nepal';
$meta_description = isset($page_description) ? $page_description : 'Bagaicha — Premium handcrafted bonsai trees from Nepal. Shop our curated collection of living art, grown by expert arborists and delivered to your doorstep.';
?>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
<title><?php echo htmlspecialchars($meta_title); ?></title>

<!-- Google Fonts: Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS v3 Play CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            DEFAULT: '#682d91', // Rich Bagaicha Purple
            dark: '#4e1f70',
            light: '#f3e8ff',
          },
          brand: {
            dark: '#1c1c2e', // Deep midnight brand color
          }
        },
        fontFamily: {
          sans: ['Poppins', 'sans-serif'],
        },
      },
    },
  }
</script>

<!-- Custom App CSS containing Toast Keyframes and Custom Scrollbars -->
<link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
<script defer src="<?php echo asset('js/main.js'); ?>"></script>
