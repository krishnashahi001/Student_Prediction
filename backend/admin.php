<?php
session_start();
// Require admin session
if (empty($_SESSION['is_admin'])) {
    header('Location: /sppa/Templates/admin-login.html');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Panel</title>
    <base href="/sppa/">
    <link rel="stylesheet" href="Design/style.css">
    <link rel="stylesheet" href="Design/admin.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="nav-left">
            <button onclick="navigateTo('Templates/Index.html')">
                <img src="components/icons/home.png" class="icon"> Home
            </button>
        </div>
        <div class="nav-right">
            <button onclick="navigateTo('backend/admin_logout.php')">
                <img src="components/icons/signout.png" class="icon"> Logout
            </button>
        </div>
    </nav>

    <h1>Admin Panel</h1>

    <!-- Editor Section -->
    <div id="editorArea" class="editor-section">
        <h2>Edit Student</h2>
        <form id="editForm">
            <input type="hidden" id="editId">
            <div><label>Name: <input type="text" id="editName"></label></div>
            <div><label>Email: <input type="email" id="editEmail"></label></div>
            <div><label>Contact: <input type="text" id="editContact"></label></div>
            <div><label>Stream: <input type="text" id="editStream"></label></div>
            <div style="margin-top:10px;">
                <button type="button" onclick="saveStudent()" class="btn-save">Save</button>
                <button type="button" onclick="closeEditor()" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Stream Filter Section -->
    <div class="stream-section">
        <h2>🔍 Students by Stream</h2>
        <div class="stream-controls">
            <select id="streamFilter" class="stream-select">
                <option value="">-- Select a Stream --</option>
            </select>
            <button onclick="filterByStream()" class="btn-filter">Search</button>
            <button onclick="resetStreamFilter()" class="btn-reset">Reset</button>
        </div>
        <div id="streamResultsContainer" class="stream-results">
            <table id="streamTable">
                <thead>
                    <tr><th>Roll No</th><th>Name</th><th>Email</th><th>Contact</th><th>Stream</th><th>Edit</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="empty-state">Select a stream to view students</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- load common helpers first -->
    <script src="javascript/script.js"></script>
    <script src="javascript/admin.js"></script>
</body>
</html>