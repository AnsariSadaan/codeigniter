<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css" integrity="sha512-9xKTRVabjVeZmc+GUW8GgSmcREDunMM+Dt/GrzchfN8tkwHizc5RP4Ok/MXFFy5rIjJjzhndFScTceq5e6GvVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Dashboard</title>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    
    <!-- Dashboard Table Container -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">User Dashboard</h1>

        <!-- Table Start -->
        <table class="min-w-full table-auto border-collapse">
            <thead>
                <tr class="bg-indigo-600 text-white">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row) { ?>
                    <tr class="border-b">
                        <td class="px-4 py-2"><?php echo $row->id; ?></td>
                        <td class="px-4 py-2"><?php echo $row->name; ?></td>
                        <td class="px-4 py-2"><?php echo $row->email; ?></td>
                        <td class="px-4 py-2 text-center">
                            <!-- Edit Button with Data -->
                            <button 
                                class="bg-blue-500 text-white py-1 px-4 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2"
                                onclick="openEditModal(<?php echo $row->id; ?>, '<?php echo $row->name; ?>', '<?php echo $row->email; ?>')">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>

                            <!-- Delete Button with Data -->
                            <button 
                                class="bg-red-500 text-white py-1 px-4 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                                onclick="confirmDelete(<?php echo $row->id; ?>)">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Table End -->

        <!-- Logout Link -->
        <a href="/logout" class="text-white px-4 py-1 rounded-lg bg-red-600 mt-4 inline-block">Logout</a>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="absolute w-full m-auto flex bg-gray-500 bg-opacity-50 hidden h-screen justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-4">Edit User</h2>
            <form id="editForm" action="/update-user" method="POST">
                <input type="hidden" name="id" id="editId">
                <div class="mb-4">
                    <label for="editName" class="block text-gray-700">Name</label>
                    <input type="text" name="name" id="editName" class="w-full p-2 border border-gray-300 rounded mt-2" required>
                </div>
                <div class="mb-4">
                    <label for="editEmail" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="editEmail" class="w-full p-2 border border-gray-300 rounded mt-2" required>
                </div>
                <div class="flex justify-between">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the edit modal and pre-fill the form
        function openEditModal(id, name, email) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Close the edit modal
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Confirm deletion and send delete request
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                // Send DELETE request to backend
                window.location.href = '/delete-user/' + id;
            }
        }
    </script>

</body>
</html>
