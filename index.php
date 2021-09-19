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

$productsByCategory = "SELECT products.ProductID, products.CategoryID, categories.CategoryName, sum(order_details.Quantity) as totalOrder FROM order_details
INNER JOIN products
on order_details.ProductID=products.ProductID
INNER JOIN categories
on products.CategoryID=categories.CategoryID
GROUP BY categories.CategoryName";


// $totalSaleByDay = $conn->query($saleByDay);
$totalSaleByDay = mysqli_query($conn, $saleByDay);
$totalOrder = mysqli_query($conn, $orders);
$percentageCategory = mysqli_query($conn, $productsByCategory);

$totalSale = 0;
$totalQuantity = 0;
$totalDiscount = 0;

while($row = mysqli_fetch_assoc($totalOrder)) {
    $totalSale = $row['TotalSale'];
    $totalQuantity = $row['Quantity'];
    $totalDiscount = $row['Discount'];
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
  </div>
</div>

<?php 
$totalSalesDay = [];
  while($row = mysqli_fetch_assoc($totalSaleByDay)) {
    // echo explode(" ",$row);
    $day = $row['byDay'];
    $price = $row['UnitPrice'];
    $totalSalesDay[$day] = $price; 
  }

  $categoryName = [];
  $categorySales = [];
  while($row = mysqli_fetch_assoc($percentageCategory)) {
    $categoryName[$row['CategoryID']] =  $row['CategoryName'];
    $categorySales[$row['CategoryID']] = $row['totalOrder'];
  }  
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
        data: [
          <?php 
            for($i=0; $i < 30; $i++) { 
              echo str_replace('.', '',$totalSalesDay[$i+1]).',';
            }
          ?>
          ]
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
</script>
</body>
</html>
<?php
mysqli_close($con);
?>


