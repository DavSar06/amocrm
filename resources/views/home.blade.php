<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmoCRM Laravel</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .header-title {
            margin: 0;
        }
        .header-buttons {
            margin-left: 20px;
        }
        .header-buttons a {
            margin-left: 10px;
            color: white;
            text-decoration: none;
        }
        .header-buttons a:hover {
            text-decoration: underline;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .items-per-page {
            width: 50px; /* Adjust the width as needed */
            height: 38px; /* Adjust the height to match the line-height of the input */
            padding: 5px;
            font-size: 14px;
            margin-right: 10px;
            border-radius: 4px; /* Make the corners rounded */
        }

        .items-per-page option {
            padding: 5px;
            font-size: 14px;
        }
    </style>

</head>
<body>
<div class="container-fluid p-0">
    <div class="header">
        <div class="header-title">{{$baseDomain}}</div>
        <div class="header-buttons">
            @if(!$oauth2access)
                <a href="{{route('amocrm.login')}}" class="btn btn btn-outline-light">Login</a>
            @else
                <a href="{{route('amocrm.logout')}}" class="btn btn btn-outline-light">Logout</a>
                <button id="getLeadsButton" class="btn btn-outline-light">Get Leads</button>
            @endif

        </div>
    </div>

    @if($oauth2access)
        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="searchBox" placeholder="Search leads...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Contacts</th>
                        <th scope="col">Last Updated</th>
                    </tr>
                    </thead>
                    <tbody id="leadsTable">
                    <!-- Leads will be inserted here -->
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation example">

                <ul class="pagination justify-content-center">

                    <select id="itemsPerPageSelect" class="form-control items-per-page" onchange="handleItemsPerPageChange()">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>

                    <li class="page-item"><a class="page-link" href="#" onclick="prevPage()">Previous</a></li>
                    <li class="page-item active"><a class="page-link" id="linkNumber" href="#" onclick="renderPage(1)">1</a></li>
                    <li class="page-item"><a class="page-link" href="#" onclick="nextPage()">Next</a></li>
                </ul>
            </nav>
        </div>
    @else
        <div class="container mt-3">
            <div class="d-flex justify-content-center">
                <h3>Login Through AmoCRM To Get Access To Data</h3>
            </div>
        </div>
    @endif
</div>

<!-- Include Bootstrap JS and jQuery -->
@if($oauth2access)
<script>
    let leads = [];
    let currentPage = 1;
    let leadsPerPage = 10;

    function fetchLeads() {
        fetch("/amocrm/get_leads")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                const leadsBody = document.getElementsByTagName('tbody')[0];
                console.log(leadsBody);
                leadsBody.innerHTML = ''; // Clear existing table rows

                data.forEach(lead => {
                    leads.push(lead);
                });
                renderPage(currentPage)
            })
            .catch(error => {
                alert(error);
            });
    }

    function handleItemsPerPageChange() {
        leadsPerPage = parseInt(document.getElementById('itemsPerPageSelect').value);
        currentPage = 1;
        renderPage(currentPage);
    }

    function applySearch() {
        const searchValue = document.getElementById('searchBox').value.toLowerCase();
        const filteredLeads = leads.filter(lead =>
            Object.values(lead).some(value =>
                value && value.toString().toLowerCase().includes(searchValue))
        );
        renderPage(currentPage, filteredLeads);
    }

    function renderPage(page, leadsToRender = leads) {
        const start = (page - 1) * leadsPerPage;
        const end = start + leadsPerPage;
        const visibleLeads = leadsToRender.slice(start, end);
        const tableBody = document.getElementsByTagName('tbody')[0];
        tableBody.innerHTML = ''; // Clear existing table rows

        visibleLeads.forEach(lead => {
            let row = tableBody.insertRow();
            row.insertCell(0).innerHTML = lead.name;
            row.insertCell(1).innerHTML = lead.status;
            row.insertCell(2).innerHTML = lead.contacts?lead.contacts:"Not found";
            row.insertCell(3).innerHTML = lead.updatedAt;
        });

        document.querySelector('#linkNumber').textContent = page;
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    }

    function nextPage() {
        if(currentPage*leadsPerPage < leads.length)
        currentPage++;
        renderPage(currentPage);
    }

    document.getElementById('searchBox').addEventListener('input', () => {
        setTimeout(applySearch, 300); // Debounce search
    });

    document.getElementById('getLeadsButton').addEventListener('click', fetchLeads);
</script>
@endif
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
