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
        <!-- Logout Link -->
        <h1 class="text-3xl font-semibold text-center text-gray-800">User Dashboard</h1>
        <h1>CSV data upload</h1>
        <input type="file" >
        <button class="text-white px-4 py-1 rounded-lg bg-green-600 inline-block">Upload</button>
        <div class="flex justify-between items-center mb-6">
            <input type="search" id="searchInput" onkeyup="filterTable()" placeholder="Search" class="px-4 py-1 border focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent rounded-lg outline-none">
            <div>
                <!-- <label for="education" class="bg-yellow-800 rounded-lg px-3 py-1">Filter</label> -->
                <select name="qualification" onchange="filterByQualification()" id="qualification" class="bg-indigo-600 text-white px-2 py-1 rounded-lg outline-none">

                    <option class="bg-white text-black" value="">All</option>
                    <option class="bg-white text-black" value="be">BE</option>
                    <option class="bg-white text-black" value="bcom">BCOM</option>
                    <option class="bg-white text-black" value="bsc">BSC</option>
                    <option class="bg-white text-black" value="bscit">BSC-IT</option>
                    <option class="bg-white text-black" value="mscit">MS-CIT</option>
                    <option class="bg-white text-black" value="btech">B-TECH</option>
                </select>
                <a href="/logout" class="text-white px-4 py-1 rounded-lg bg-red-600 inline-block">Logout</a>
                <!-- <a onclick="dowloadData()" class="text-white px-4 py-1 rounded-lg bg-green-600 inline-block"><i class="fa-solid fa-download"></i></a> -->
                <!-- <a href="" onclick="downloadData()" class="text-white px-4 py-1 rounded-lg bg-green-600 inline-block">
                    <i class="fa-solid fa-download"></i>
                </a> -->
                <a href="<?= base_url('/download-users') ?>" class="text-white px-4 py-1 rounded-lg bg-green-600 inline-block" ><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Table Start -->
        <table class="min-w-full table-auto border-collapse" id='myTable'>
            <thead>
                <tr class="bg-indigo-600 text-white">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left hidden">MongoId</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Age</th>
                    <th class="px-4 py-2 text-left">Qualification</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row) {
                    // print_r($users); die;
                ?>

                    <tr class="border-b">
                        <td class="px-4 py-2"><?php echo $row->id; ?></td>
                        <td class="px-4 py-2 hidden"><?php echo $row->mongoId; ?></td>
                        <td class="px-4 py-2"><?php echo $row->name; ?></td>
                        <td class="px-4 py-2"><?php echo $row->age; ?></td>
                        <td class="px-4 py-2"><?php echo $row->qualification; ?></td>
                        <td class="px-4 py-2"><?php echo $row->email; ?></td>
                        <td class="px-4 py-2 text-center">
                            <!-- Edit Button with Data -->
                            <button
                                class="bg-blue-500 text-white py-1 px-4 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2"
                                onclick="openEditModal(<?php echo $row->id; ?>, '<?php echo $row->name; ?>', '<?php echo $row->age; ?>', '<?php echo $row->qualification; ?>', '<?php echo $row->email; ?>', '<?php echo $row->mongoId; ?>')">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Delete Button with Data -->
                            <button
                                class="bg-red-500 text-white py-1 px-4 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                                onclick="confirmDelete(<?php echo $row->id; ?>, '<?php echo $row->mongoId; ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Table End -->
        <h1 id="noDataMessage" class="text-xl text-center text-gray-500 hidden">No Data Found</h1>

        <!-- pagination start -->
        <div class="flex justify-center mt-6">
            <nav aria-label="Page navigation example">
                <ul class="inline-flex -space-x-px">
                    <?php if ($currentPage > 1): ?>
                        <li>
                            <a href="/dashboard?page=<?php echo $currentPage - 1; ?>&searchQuery=<?php echo urlencode($searchQuery); ?>" class="px-4 py-2 text-indigo-600 hover:text-indigo-900">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="/dashboard?page=<?php echo $i; ?>&searchQuery=<?php echo urlencode($searchQuery); ?>" class="px-4 py-2 text-indigo-600 hover:text-indigo-900 <?php echo ($i == $currentPage) ? 'font-bold' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li>
                            <a href="/dashboard?page=<?php echo $currentPage + 1; ?>&searchQuery=<?php echo urlencode($searchQuery); ?>" class="px-4 py-2 text-indigo-600 hover:text-indigo-900">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <!-- pagination end -->

    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="absolute w-full m-auto flex bg-gray-500 bg-opacity-50 hidden h-screen justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-4">Edit User</h2>
            <form id="editForm" action="/update-user" method="POST">
                <div class="mb-4">
                    <label for="editId" class="block text-gray-700">Id</label>
                    <input type="number" name="id" id="editId" class="w-full p-2 border border-gray-300 rounded mt-2" readonly>
                </div>
                <div class="mb-4 hidden">
                    <label for="editmongoId" class="block text-gray-700">mongoId</label>
                    <input type="text" name="mongoId" id="editmongoId" class="w-full p-2 border border-gray-300 rounded mt-2" readonly>
                </div>
                <div class="mb-4">
                    <label for="editName" class="block text-gray-700">Name</label>
                    <input type="text" name="name" id="editName" class="w-full p-2 border border-gray-300 rounded mt-2" required>
                </div>

                <div class="mb-4">
                    <label for="editAge" class="block text-gray-700">Age</label>
                    <input type="text" name="age" id="editAge" class="w-full p-2 border border-gray-300 rounded mt-2" required>
                </div>

                <div class="mb-4">
                    <label for="editQualification" class="block text-gray-700">Qualification</label>
                    <input type="text" name="qualification" id="editQualification" class="w-full p-2 border border-gray-300 rounded mt-2" required>
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
        function filterByQualification() {
            const selectedQualification = document.getElementById('qualification').value.toLowerCase().trim();
            const tableBody = document.querySelector('table tbody');
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(function(row) {
                const qualificationCell = row.querySelector('td:nth-child(5)'); // Adjust the index if the column order changes

                if (qualificationCell) {
                    const qualification = qualificationCell.textContent.toLowerCase();
                    if (selectedQualification === "" || qualification === selectedQualification) {
                        row.style.display = ''; // Show the row
                    } else {
                        row.style.display = 'none'; // Hide the row
                    }
                }
            });

            // Update the "No Data Found" message visibility
            const noDataMessage = document.getElementById('noDataMessage');
            const visibleRows = Array.from(rows).some(row => row.style.display !== 'none');
            if (visibleRows) {
                noDataMessage.classList.add('hidden');
            } else {
                noDataMessage.classList.remove('hidden');
            }
        }

        // function downloadData() {
        //     const tableBody = document.querySelector('table tbody');
        //     const rows = tableBody.querySelectorAll('tr');
        //     const csvRows = [];

        //     // Get table headers
        //     const header = document.querySelector('table thead');
        //     const headers = header.querySelectorAll('th');
        //     const headerRow = [];
        //     headers.forEach(headerCell => {
        //         headerRow.push(headerCell.innerText);
        //     });
        //     csvRows.push(headerRow.join(',')); // Add header row to CSV

        //     // Loop through each row and extract data
        //     rows.forEach(row => {
        //         const cells = row.querySelectorAll('td');
        //         const rowData = [];
        //         cells.forEach(cell => {
        //             rowData.push(cell.innerText); // Extract cell text
        //         });
        //         csvRows.push(rowData.join(',')); // Join row data with commas
        //     });

        //     // Convert to CSV string
        //     const csvData = csvRows.join('\n');

        //     // Create a Blob with CSV data
        //     const blob = new Blob([csvData], {
        //         type: 'text/csv'
        //     });

        //     // Create a temporary link to trigger download
        //     const link = document.createElement('a');
        //     link.href = URL.createObjectURL(blob);
        //     link.download = 'users_data.csv'; // Filename for download

        //     // Programmatically click the link to trigger the download
        //     link.click();
        // }




        function filterTable() {
            const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
            const tableBody = document.querySelector('table tbody');
            const rows = tableBody.querySelectorAll('tr');
            let found = false;

            rows.forEach(function(row) {
                const nameCell = row.querySelector('td:nth-child(3)');
                const emailCell = row.querySelector('td:nth-child(4)');

                if (nameCell) { // Make sure nameCell exists
                    const name = nameCell.textContent.toLowerCase();
                    const email = emailCell.textContent.toLowerCase();
                    if ((name.indexOf(searchQuery) > -1) || (email.indexOf(searchQuery) > -1)) {
                        row.style.display = '';
                        found = true;
                    } else {
                        row.style.display = 'none';
                    }
                }
            });

            // If no row is found, display "No Data Found" message
            const noDataMessage = document.getElementById('noDataMessage');
            if (found) {
                noDataMessage.classList.add('hidden'); // Hide "No Data Found" message
            } else {
                noDataMessage.classList.remove('hidden'); // Show "No Data Found" message
            }
        }


        // Open the edit modal and pre-fill the form
        function openEditModal(id, name, age, qualification, email, mongoId) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editAge').value = age;
            document.getElementById('editQualification').value = qualification;
            document.getElementById('editEmail').value = email;
            document.getElementById('editmongoId').value = mongoId;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Close the edit modal
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Confirm deletion and send delete request
        function confirmDelete(id, mongoId) {
            if (confirm('Are you sure you want to delete this user?')) {
                // Send DELETE request to backend
                window.location.href = '/delete-user/' + id + '/' + mongoId;
            }
        }
    </script>
    <!-- <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
        })
    </script>
    <script src="https://cdn.datatables.net/2.1.8/js/jquery.dataTables.min.js"></script> -->
</body>

</html>