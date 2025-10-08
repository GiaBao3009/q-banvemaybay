<?php
session_start();
require_once 'app/config/database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị Viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
    
</style>
<body>
<div class="sidebar">
    <h2>Quản Lý</h2>
    <ul>
        <li><a href="#" id="createStaffBtn"><i class="fas fa-user-plus"></i> Cấp tài khoản nhân viên</a></li>
        <li><a href="#" id="toggleStaffBtn"><i class="fas fa-user-tie"></i> Quản lý nhân viên</a></li>
        <li><a href="#" id="manageCustomersBtn"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="#" id="manageBookingsBtn"><i class="fas fa-receipt"></i> Quản lý thông tin đặt vé</a></li>
        <li><a href="#" id="manageFlightsBtn"><i class="fas fa-plane"></i> Quản lý chuyến bay</a></li>
        <li><a href="#" id="revenueBtn"><i class="fas fa-dollar-sign"></i> Doanh thu</a></li>
        <li><a href="#" id="statisticsBtn"><i class="fas fa-chart-line"></i> Thống kê</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
    </ul>
</div>
<main class="container">
    <div id="staffSection" class="content-section">
        <div class="section-header">Danh sách nhân viên</div>
        <input type="text" id="staffSearchInput" placeholder="Tìm kiếm Username...">
        <table>
            <thead>
                <tr><th>ID</th><th>Họ & Tên</th><th>Username</th><th>Email</th><th>Điện thoại</th><th>Vai trò</th><th>Xóa</th></tr>
            </thead>
            <tbody id="staffList"></tbody>
        </table>
    </div>
    <div id="customersSection" class="content-section">
        <div class="section-header">Danh sách khách hàng</div>
        <input type="text" id="customerPhoneSearch" placeholder="Tìm kiếm số điện thoại...">
        <table>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Họ & Tên</th><th>Email</th><th>Điện thoại</th><th>Ngân hàng</th><th>Xóa</th></tr>
            </thead>
            <tbody id="customerList"></tbody>
        </table>
    </div>
    <div id="flightsSection" class="content-section">
        <div class="section-header">Danh sách chuyến bay</div>
        <div id="addFlightWrapper">
            <button id="addFlightBtn"><i class="fa fa-plus"></i> Thêm chuyến bay</button>
            <div id="addFlightForm">
                <input type="text" id="newAirlineName" placeholder="Tên hãng">
                <input type="text" id="newAirlineCode" placeholder="Mã hãng">
                <input type="text" id="newCountry" placeholder="Quốc gia">
                <input type="time" id="newFlightStartTime">
                <input type="time" id="newFlightEndTime">
                <input type="number" id="newTicketPrice" placeholder="Giá vé">
                <button id="saveNewFlightBtn">Lưu chuyến bay</button>
            </div>
        </div>
        <input type="text" id="flightSearchInput" placeholder="Tìm kiếm hãng hàng không...">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên hãng</th>
                    <th>Mã hãng</th>
                    <th>Quốc gia</th>
                    <th>Giờ bắt đầu</th>
                    <th>Giờ kết thúc</th>
                    <th>Giá vé</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody id="airlinesList">
                <?php
                $query = "SELECT * FROM airlines";
                $stmt = $pdo->query($query);
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td class='editable' data-column='airline_id'>" . htmlspecialchars($row['airline_id']) . "</td>";
                    echo "<td class='editable' data-column='airline_name'>" . htmlspecialchars($row['airline_name']) . "</td>";
                    echo "<td class='editable' data-column='airline_code'>" . htmlspecialchars($row['airline_code']) . "</td>";
                    echo "<td class='editable' data-column='country'>" . htmlspecialchars($row['country']) . "</td>";
                    echo "<td class='editable' data-column='flight_start_time'>" . htmlspecialchars($row['flight_start_time']) . "</td>";
                    echo "<td class='editable' data-column='flight_end_time'>" . htmlspecialchars($row['flight_end_time']) . "</td>";
                    echo "<td class='editable' data-column='ticket_price'>" . htmlspecialchars($row['ticket_price']) . "</td>";
                    echo "<td><button class='delete-btn' data-id='" . $row['airline_id'] . "'>Xóa</button></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div id="bookingsSection" class="content-section">
        <div class="section-header">Danh sách thông tin đặt vé</div>
        <input type="text" id="bookingSearchInput" placeholder="Tìm kiếm mã đặt vé...">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên khách hàng</th>
                    <th>Loại vé</th>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Ngày đi</th>
                    <th>Ngày về</th>
                    <th>Người lớn</th>
                    <th>Trẻ em</th>
                    <th>Em bé</th>
                    <th>Hãng bay</th>
                    <th>Giờ khởi hành</th>
                    <th>Giờ kết thúc</th>
                    <th>Giá vé</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody id="bookingList"></tbody>
        </table>
    </div>
    <div id="revenueSection" class="content-section">
        <div class="section-header">Doanh thu theo tháng</div>
        <div class="filter-group">
            <select id="yearFilter">
                <option value="">Tất cả các năm</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
            <select id="monthFilter">
                <option value="">Tất cả các tháng</option>
                <option value="01">Tháng 1</option>
                <option value="02">Tháng 2</option>
                <option value="03">Tháng 3</option>
                <option value="04">Tháng 4</option>
                <option value="05">Tháng 5</option>
                <option value="06">Tháng 6</option>
                <option value="07">Tháng 7</option>
                <option value="08">Tháng 8</option>
                <option value="09">Tháng 9</option>
                <option value="10">Tháng 10</option>
                <option value="11">Tháng 11</option>
                <option value="12">Tháng 12</option>
            </select>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tháng</th>
                    <th>Tổng doanh thu (VND)</th>
                    <th>Thay đổi</th>
                </tr>
            </thead>
            <tbody id="revenueList"></tbody>
        </table>
    </div>
    <div id="statisticsSection" class="content-section">
        <div class="section-header">Thống kê doanh thu</div>
        <div class="filter-group">
            <select id="statsYearFilter">
                <option value="">Tất cả các năm</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </div>
        <canvas id="revenueChart"></canvas>
    </div>
</main>
<script>
$(document).ready(function() {
    let revenueChart;

    function hideAllSections() { $(".content-section").hide(); }

    function loadStaffList() {
        $.get("get_staff.php", function(response) {
            if (response.success) {
                let staffHtml = response.data.map(staff => `
                    <tr>
                        <td>${staff.id}</td>
                        <td>${staff.full_name || "Chưa cập nhật"}</td>
                        <td>${staff.username}</td>
                        <td>${staff.email || "Chưa cập nhật"}</td>
                        <td>${staff.phone || "Chưa cập nhật"}</td>
                        <td>${staff.role}</td>
                        <td><button class="delete-btn" data-id="${staff.id}">Xóa</button></td>
                    </tr>`).join("");
                $("#staffList").html(staffHtml);
                $("#staffSection").show();
            }
        }, "json");
    }
    $("#toggleStaffBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#staffSection").show();
        loadStaffList();
    });

    function loadCustomerList() {
        $.get("get_customers.php", function(response) {
            if (response.success) {
                let customerHtml = response.data.map(customer => `
                    <tr>
                        <td>${customer.id}</td>
                        <td>${customer.username}</td>
                        <td>${customer.full_name}</td>
                        <td>${customer.email}</td>
                        <td>${customer.phone}</td>
                        <td>${customer.bank_name}</td>
                        <td><button class="delete-btn" data-id="${customer.id}">Xóa</button></td>
                    </tr>`).join("");
                $("#customerList").html(customerHtml);
                $("#customersSection").show();
            }
        }, "json");
    }
    $("#manageCustomersBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#customersSection").show();
        loadCustomerList();
    });

    $("#manageBookingsBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#bookingsSection").show();
        loadBookingsList();
    });

    function loadBookingsList() {
        $.get("getHoaDon.php", function(response) {
            if (response.success) {
                let bookingHtml = response.data.map(booking => `
                    <tr>
                        <td class="editable" data-column="ma_hoadon" data-id="${booking.ma_hoadon}">${booking.ma_hoadon}</td>
                        <td class="editable" data-column="name" data-id="${booking.ma_hoadon}">${booking.name || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="ticket_type" data-id="${booking.ma_hoadon}">${booking.ticket_type === 'one_way' ? 'Một chiều' : 'Khứ hồi'}</td>
                        <td class="editable" data-column="departure" data-id="${booking.ma_hoadon}">${booking.departure || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="destination" data-id="${booking.ma_hoadon}">${booking.destination || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="departure_date" data-id="${booking.ma_hoadon}">${booking.departure_date || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="return_date" data-id="${booking.ma_hoadon}">${booking.return_date || "Không có"}</td>
                        <td class="editable" data-column="adults" data-id="${booking.ma_hoadon}">${booking.adults || 0}</td>
                        <td class="editable" data-column="children" data-id="${booking.ma_hoadon}">${booking.children || 0}</td>
                        <td class="editable" data-column="infants" data-id="${booking.ma_hoadon}">${booking.infants || 0}</td>
                        <td class="editable" data-column="airline_name" data-id="${booking.ma_hoadon}">${booking.airline_name || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="flight_start_time" data-id="${booking.ma_hoadon}">${booking.flight_start_time || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="flight_end_time" data-id="${booking.ma_hoadon}">${booking.flight_end_time || "Chưa cập nhật"}</td>
                        <td class="editable" data-column="ticket_price" data-id="${booking.ma_hoadon}">${booking.ticket_price || "0.00"}</td>
                        <td><button class="delete-btn" data-id="${booking.ma_hoadon}">Xóa</button></td>
                    </tr>`).join("");
                $("#bookingList").html(bookingHtml);
            } else {
                $("#bookingList").html("<tr><td colspan='15'>Không có dữ liệu</td></tr>");
            }
        }, "json");
    }

    $("#manageFlightsBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#flightsSection").show();
        loadAirlinesList();
    });

    $("#addFlightBtn").click(function() {
        $("#addFlightForm").toggle();
    });

    $("#saveNewFlightBtn").click(function(e) {
        e.preventDefault();
        let airlineName = $("#newAirlineName").val().trim();
        let airlineCode = $("#newAirlineCode").val().trim();
        let country = $("#newCountry").val().trim();
        let flightStartTime = $("#newFlightStartTime").val();
        let flightEndTime = $("#newFlightEndTime").val();
        let ticketPrice = $("#newTicketPrice").val().trim();

        if (!airlineName || !airlineCode || !country || !flightStartTime || !flightEndTime || !ticketPrice) {
            alert("Vui lòng điền đầy đủ thông tin!");
            return;
        }

        $.post("add_airline.php", {
            airline_name: airlineName,
            airline_code: airlineCode,
            country: country,
            flight_start_time: flightStartTime,
            flight_end_time: flightEndTime,
            ticket_price: ticketPrice
        }, function(response) {
            if (response.success) {
                loadAirlinesList();
                $("#addFlightForm").hide();
                $("#newAirlineName, #newAirlineCode, #newCountry, #newFlightStartTime, #newFlightEndTime, #newTicketPrice").val('');
            } else {
                alert("❌ Lỗi khi thêm chuyến bay!");
            }
        }, "json");
    });

    function loadAirlinesList() {
        $.get("get_airlines.php", function(response) {
            if (response.success) {
                let airlinesHtml = response.data.map(airline => `
                    <tr>
                        <td>${airline.airline_id}</td>
                        <td class="editable" data-column="airline_name" data-id="${airline.airline_id}">${airline.airline_name}</td>
                        <td class="editable" data-column="airline_code" data-id="${airline.airline_id}">${airline.airline_code}</td>
                        <td class="editable" data-column="country" data-id="${airline.airline_id}">${airline.country}</td>
                        <td class="editable" data-column="flight_start_time" data-id="${airline.airline_id}">${airline.flight_start_time}</td>
                        <td class="editable" data-column="flight_end_time" data-id="${airline.airline_id}">${airline.flight_end_time}</td>
                        <td class="editable" data-column="ticket_price" data-id="${airline.airline_id}">${airline.ticket_price}</td>
                        <td><button class="delete-btn" data-id="${airline.airline_id}">Xóa</button></td>
                    </tr>`).join("");
                $("#airlinesList").html(airlinesHtml);
            }
        }, "json");
    }

    $(document).on("click", ".delete-btn", function() {
        let id = $(this).data("id");
        let section = $(this).closest('.content-section').attr('id');
        let url = section === 'staffSection' ? 'delete_staff.php' : 
                 section === 'customersSection' ? 'delete_customer.php' : 
                 section === 'bookingsSection' ? 'delete_booking.php' : 
                 'delete_airline.php';
        if (!confirm("Bạn có chắc muốn xóa mục này?")) return;
        let $row = $(this).closest("tr");
        $.post(url, { id: id }, function(response) {
            if (response.success) {
                alert("✅ Xóa thành công!");
                $row.remove();
            } else {
                alert("❌ Lỗi khi xóa!");
            }
        }, "json");
    });

    $("#createStaffBtn").click(function(e) {
        e.preventDefault();
        $.post("create_staff.php", function(response) {
            if (response.success) {
                alert(`✅ Tạo tài khoản thành công!\nUsername: ${response.username}\nPassword: 123`);
                $("#toggleStaffBtn").trigger("click");
            } else {
                alert(response.message || "❌ Lỗi khi tạo tài khoản!");
            }
        }, "json");
    });

    $("#staffSearchInput").on("keyup", function() {
        let searchQuery = $(this).val().toLowerCase();
        $("#staffList tr").filter(function() {
            let username = $(this).find("td:eq(2)").text().toLowerCase();
            $(this).toggle(username.indexOf(searchQuery) > -1);
        });
    });

    $("#customerPhoneSearch").on("keyup", function() {
        let searchQuery = $(this).val().toLowerCase();
        $("#customerList tr").filter(function() {
            let phone = $(this).find("td:eq(4)").text().toLowerCase();
            $(this).toggle(phone.indexOf(searchQuery) > -1);
        });
    });

    $("#flightSearchInput").on("keyup", function() {
        let searchQuery = $(this).val().toLowerCase();
        $("#airlinesList tr").filter(function() {
            let airlineName = $(this).find("td:eq(1)").text().toLowerCase();
            $(this).toggle(airlineName.indexOf(searchQuery) > -1);
        });
    });

    $("#bookingSearchInput").on("keyup", function() {
        let searchQuery = $(this).val().toLowerCase();
        $("#bookingList tr").filter(function() {
            let bookingId = $(this).find("td:eq(0)").text().toLowerCase();
            $(this).toggle(bookingId.indexOf(searchQuery) > -1);
        });
    });

    $(document).on('click', '.editable', function() {
        var $this = $(this);
        var currentText = $this.text();
        var columnName = $this.data('column');
        var inputType = (columnName === 'flight_start_time' || columnName === 'flight_end_time') ? 'time' :
                        (columnName === 'adults' || columnName === 'children' || columnName === 'infants' || columnName === 'ticket_price') ? 'number' :
                        (columnName === 'departure_date' || columnName === 'return_date') ? 'date' : 'text';

        var $input = $('<input>', {
            type: inputType,
            value: currentText,
            blur: function() {
                var newText = $input.val();
                $this.text(newText);
                updateColumnData($this);
            },
            keyup: function(e) {
                if (e.key === 'Enter') {
                    var newText = $input.val();
                    $this.text(newText);
                    updateColumnData($this);
                } else if (e.key === 'Escape') {
                    $this.text(currentText);
                }
            }
        });
        $this.empty().append($input);
        $input.focus();
    });

    function updateColumnData($column) {
        var columnName = $column.data('column');
        var newValue = $column.text();
        var id = $column.data('id');
        var isBooking = $column.closest('#bookingsSection').length > 0;
        var url = isBooking ? 'update_booking.php' : 'update_airline.php';
        var data = isBooking ? { ma_hoadon: id, column_name: columnName, new_value: newValue } 
                            : { airline_id: id, column_name: columnName, new_value: newValue };

        $.post(url, data, function(response) {
            if (response.success) {
                alert('✅ Cập nhật thành công!');
            } else {
                alert('❌ Có lỗi xảy ra khi cập nhật dữ liệu: ' + (response.message || 'Lỗi không xác định'));
            }
        }, 'json').fail(function() {
            alert('❌ Lỗi kết nối server!');
        });
    }

    $("#revenueBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#revenueSection").show();
        loadRevenue();
    });

    function loadRevenue() {
        let year = $("#yearFilter").val();
        let month = $("#monthFilter").val();
        $.get("get_revenue.php", { year: year, month: month }, function(response) {
            if (response.success && response.data.length > 0) {
                let html = response.data.map(item => `
                    <tr>
                        <td>${item.month}</td>
                        <td>${Number(item.total_revenue).toLocaleString('vi-VN')}</td>
                        <td class="${item.change_class}">${item.change === null ? '-' : (item.change > 0 ? '+' : '') + Number(item.change).toLocaleString('vi-VN')}</td>
                    </tr>`).join("");
                $("#revenueList").html(html);
            } else {
                $("#revenueList").html("<tr><td colspan='3'>Không có dữ liệu</td></tr>");
            }
        }, "json");
    }

    $("#yearFilter, #monthFilter").on("change", function() {
        loadRevenue();
    });

    $("#statisticsBtn").click(function(e) {
        e.preventDefault();
        hideAllSections();
        $("#statisticsSection").show();
        loadStatistics();
    });

    function loadStatistics() {
        let year = $("#statsYearFilter").val();
        $("#revenueChart").remove();
        $("#statisticsSection").append('<canvas id="revenueChart"></canvas>');

        $.get("get_revenue.php", { year: year }, function(response) {
            if (response.success && response.data.length > 0) {
                let labels = response.data.map(item => item.month);
                let data = response.data.map(item => parseFloat(item.total_revenue));

                let ctx = document.getElementById('revenueChart').getContext('2d');
                revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Doanh thu (VND)',
                            data: data,
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3498db',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#3498db'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Doanh thu (VND)' },
                                ticks: { callback: function(value) { return Number(value).toLocaleString('vi-VN'); } }
                            },
                            x: { title: { display: true, text: 'Tháng' } }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Doanh thu: ${Number(context.parsed.y).toLocaleString('vi-VN')} VND`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                $("#revenueChart").replaceWith("<p>Không có dữ liệu để hiển thị biểu đồ.</p>");
            }
        }, "json").fail(function(jqXHR, textStatus, errorThrown) {
            $("#revenueChart").replaceWith("<p>Lỗi kết nối: " + textStatus + "</p>");
        });
    }

    $("#statsYearFilter").on("change", function() {
        loadStatistics();
    });
});
</script>
</body>
</html>