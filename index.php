<?php
session_start();
if(empty($_SESSION['logged_in'])) {
  header('Location: /login.php');
}

$servername = "localhost:3306";
$username = "default";
$password = "secret";
$dbname = "default";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
// get all orders
$orders = "SELECT orders.OrderID, month(orders.OrderDate) as OrderMonth, year(orders.OrderDate) as OrderYear, sum(order_details.UnitPrice) as TotalSale, sum(order_details.Quantity) as Quantity, sum(order_details.Discount) as Discount FROM order_details
INNER JOIN orders
on order_details.OrderID=orders.OrderID
WHERE orders.OrderDate BETWEEN '1995-05-01 00:00:00' AND '1995-05-30 00:00:00'
GROUP BY month(orders.OrderDate)
";


$saleByDay = "SELECT orders.OrderID, day(orders.OrderDate) as byDay, sum(order_details.UnitPrice) as UnitPrice, order_details.Quantity, order_details.Discount FROM order_details
INNER JOIN orders
on order_details.OrderID=orders.OrderID
WHERE orders.OrderDate BETWEEN '1995-05-01 00:00:00' AND '1995-05-30 00:00:00'
GROUP BY orders.OrderDate
";

$productsByCategory = "SELECT products.ProductID, orders.OrderDate, products.CategoryID, categories.CategoryName, sum(order_details.Quantity) as totalOrder FROM order_details
INNER JOIN products
on order_details.ProductID=products.ProductID
INNER JOIN categories
on products.CategoryID=categories.CategoryID
INNER JOIN orders
on orders.OrderID=order_details.OrderID
WHERE orders.OrderDate BETWEEN '1995-05-01 00:00:00' AND '1995-05-30 00:00:00'
GROUP BY categories.CategoryName";

$customerOrder = "SELECT customers.CustomerID, sum(order_details.UnitPrice) as Sales from order_details
INNER JOIN orders on orders.OrderID=order_details.OrderID
INNER JOIN customers on orders.CustomerID=customers.CustomerID
WHERE orders.OrderDate BETWEEN '1995-05-01 00:00:00' AND '1995-05-30 00:00:00'
GROUP BY customers.CustomerID
LIMIT 7"; 


// $totalSaleByDay = $conn->query($saleByDay);
$totalSaleByDay = mysqli_query($conn, $saleByDay);
$totalOrder = mysqli_query($conn, $orders);
$percentageCategory = mysqli_query($conn, $productsByCategory);
$orderByCustomer = mysqli_query($conn, $customerOrder);

// top of dashboard
$totalSale = 0;
$totalQuantity = 0;
$totalDiscount = 0;

while($row = mysqli_fetch_assoc($totalOrder)) {
    $totalSale = $row['TotalSale'];
    $totalQuantity = $row['Quantity'];
    $totalDiscount = $row['Discount'];
}

// total sale per day
$totalSalesDay = [];
while($row = mysqli_fetch_assoc($totalSaleByDay)) {
  // echo explode(" ",$row);
  $day = $row['byDay'];
  $price = $row['UnitPrice'];
  $totalSalesDay[$day] = $price; 
}
// sale by product categories
$categoryName = [];
$categorySales = [];
while($row = mysqli_fetch_assoc($percentageCategory)) {
  $categoryName[$row['CategoryID']] =  $row['CategoryName'];
  $categorySales[$row['CategoryID']] = $row['totalOrder'];
}  

$customerOrder = [];
while($row = mysqli_fetch_assoc($orderByCustomer)) {
  // echo $row['Sales'].", ";
  $customerOrder[$row['CustomerID']] = $row['Sales'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <title>Dashboard</title>
  <style>
    body {background-color: #f0f0f0;}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
      </ul>
      <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle dropleft" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php echo $_SESSION['username']; ?>
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="logout.php">Logout</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
  <h4>Sales Dashboard</h4>
  <h6>May 1995</h6>
  <div class="row">
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-body">
          <span class="font-weight-bold">Total Sales</span>
          <div>
            USD
            <?php
            $result = $totalSale * $totalQuantity - ($totalDiscount/100 * $totalSale);
            echo number_format($result,2,",",".");
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-body">
          <span class="font-weight-bold">Total Orders</span>
          <div>
            <?php echo $totalQuantity; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div>
    <canvas id="salesByDay"></canvas>
  </div>
  <div class="row">
    <div class="col-6">
      <span>Sales By product Categories</span>
      <div>
        <canvas id="categorySales"></canvas>
      </div>
    </div>
    <div class="col-6">
      <div>
        <canvas id="customerChart"></canvas>
      </div>
    </div>
  </div>
</div>

<?php 


 
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let days = []
    for(var i = 0; i < 30; i++) {
      days.push(i+1);
    }
    const labelsBar = days;
    const dataBar = {
      labels: labelsBar,
      datasets: [{
        label: 'Total Sale Per Day',
        backgroundColor: 'rgb(255, 99, 132)',
        borderColor: 'rgb(255, 99, 132)',
        data: [<?php 
            for($i=0; $i < 30; $i++) { 
              echo str_replace('.', '',$totalSalesDay[$i+1]).',';
            }
        ?>]
      }]
    };
    const configBar = {
      type: 'bar',
      data: dataBar,
      options: {}
    }
    var salesByDay = new Chart(
      document.getElementById('salesByDay'),
      configBar
    );

    const dataPie = {
      labels: [<?php 
        foreach($categoryName as $val) {
          echo "'".$val."'".",";
        }
        ?>],
      datasets: [{
        label: 'My First Dataset',
        data: 
        [<?php 
        foreach($categorySales as $val) {
          echo "'".$val."'".",";
        }
        ?>],
        backgroundColor: [
          'rgb(255, 99, 132)',
          'rgb(54, 162, 235)',
          'rgb(34, 205, 84)',
          'rgb(56, 205, 88)',
          'rgb(12, 205, 97)',
          'rgb(87, 205, 45)',
          'rgb(90, 205, 34)',
          'rgb(100, 205, 12)'
        ],
        hoverOffset: 4
      }]
    };
    const configPie = {
      type: 'pie',
      data: dataPie,
    };
    var categorySales = new Chart(
      document.getElementById('categorySales'),
      configPie
    )

    const dataCus = {
      labels: [<?php
      foreach($customerOrder as $key=>$val) {
        echo "'".$key."'".',';
      }
    ?>],
      datasets: [{
        axis: 'y',
        label: 'Sales By Customers',
        data: [<?php
        foreach($customerOrder as $val) {
          echo "'".str_replace('.', '', $val)."'".',';
        }
        ?> ],
        fill: false,
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 159, 64, 0.2)',
          'rgba(255, 205, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(201, 203, 207, 0.2)'
        ],
        borderColor: [
          'rgb(255, 99, 132)',
          'rgb(255, 159, 64)',
          'rgb(255, 205, 86)',
          'rgb(75, 192, 192)',
          'rgb(54, 162, 235)',
          'rgb(153, 102, 255)',
          'rgb(201, 203, 207)'
        ],
        borderWidth: 1
      }]
    };
    const configCustomer = {
      type: 'bar',
      data: dataCus,
      options: {
        indexAxis: 'y',
      }
    };
      var customerChart = new Chart(
      document.getElementById('customerChart'),
      configCustomer
    )
</script>
</body>
</html>
<?php
mysqli_close($conn);
?>


