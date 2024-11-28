<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url() ?>css/styles.css?v=1.0">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css" />
    <title>Login</title>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <!-- Login Form Container -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">Login</h1>

        <!-- Form Start -->
        <form action="<?= base_url('/login') ?>" method="post">

            <!-- Email Field -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email:</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter your email" required>
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter your password" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Login
            </button>

            <!-- Signup Link -->
            <p class="text-center text-sm text-gray-600 mt-4">
                Don't have an account? <a href="<?= base_url('/signup') ?>" class="text-indigo-600 hover:text-indigo-700">Sign Up here</a>
            </p>
        </form>
        <!-- Form End -->
    </div>
    <!-- Login Form Container End -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (session()->getFlashdata('success')) { ?>
                alertify.set('notifier', 'position', 'top-right');
                alertify.success("<?= session()->getFlashdata('success') ?>");
            <?php } ?>

            <?php if (session()->getFlashdata('error')) { ?>
                alertify.set('notifier', 'position', 'top-right');
                alertify.error('<?= session()->getFlashdata('error') ?>');
            <?php } ?>
        });
    </script>
</body>

</html>