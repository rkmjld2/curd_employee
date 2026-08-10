<?php

include("db.php");

$message = "";


/*
=========================================================
DELETE
=========================================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_id"])
) {

    $id = intval($_POST["delete_id"]);

    if ($id > 0) {

        $sql = "DELETE FROM employee WHERE id = $id";

        if (mysqli_query($conn, $sql)) {

            $message = "Employee record deleted successfully.";

        } else {

            $message = "Delete failed: " . mysqli_error($conn);
        }
    }
}


/*
=========================================================
ADD / UPDATE
=========================================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["save"])
) {

    $id = isset($_POST["id"])
        ? intval($_POST["id"])
        : 0;


    $employee_name = mysqli_real_escape_string(
        $conn,
        trim($_POST["employee_name"])
    );


    $basic_pay = floatval($_POST["basic_pay"]);

    $da_percent = floatval($_POST["da_percent"]);

    $hra_percent = floatval($_POST["hra_percent"]);

    $pf_deduction = floatval($_POST["pf_deduction"]);

    $other_allowance = floatval($_POST["other_allowance"]);


    /*
    -----------------------------------------------------
    CALCULATIONS
    -----------------------------------------------------
    */

    $da_amount =
        ($basic_pay * $da_percent) / 100;


    $hra_amount =
        ($basic_pay * $hra_percent) / 100;


    $total_payment =$basic_pay
        + $da_amount
        + $hra_amount
        - $pf_deduction
        + $other_allowance;


    /*
    =====================================================
    UPDATE
    =====================================================
    */

    if ($id > 0) {

        $sql = "

        UPDATE employee SET

            Employee_name = '$employee_name',

            BASIC_PAY = $basic_pay,

            DA_PERCENT = $da_percent,

            DA_AMOUNT = $da_amount,

            HRA_PERCENT = $hra_percent,

            HRA_AMOUNT = $hra_amount,

            PF_DEDUCTION = $pf_deduction,

            ANY_OTHER_ALLOWANCE = $other_allowance,

            TOTAL_PAYMENT = $total_payment

        WHERE id = $id

        ";


        if (mysqli_query($conn, $sql)) {

            $message =
                "Employee record updated successfully.";

        } else {

            $message =
                "Update failed: " . mysqli_error($conn);
        }
    }


    /*
    =====================================================
    INSERT
    =====================================================
    */

    else {

        $sql = "

        INSERT INTO employee

        (
            Employee_name,
            BASIC_PAY,
            DA_PERCENT,
            DA_AMOUNT,
            HRA_PERCENT,
            HRA_AMOUNT,
            PF_DEDUCTION,
            ANY_OTHER_ALLOWANCE,
            TOTAL_PAYMENT
        )

        VALUES

        (
            '$employee_name',
            $basic_pay,
            $da_percent,
            $da_amount,
            $hra_percent,
            $hra_amount,
            $pf_deduction,
            $other_allowance,
            $total_payment
        )

        ";


        if (mysqli_query($conn, $sql)) {

            $message =
                "Employee record added successfully.";

        } else {

            $message =
                "Insert failed: " . mysqli_error($conn);
        }
    }
}


/*
=========================================================
EDIT RECORD
=========================================================
*/

$edit = NULL;


if (isset($_GET["edit"])) {

    $id = intval($_GET["edit"]);

    if ($id > 0) {

        $sql =
            "SELECT * FROM employee WHERE id = $id";

        $result =
            mysqli_query($conn, $sql);

        if ($result) {

            $edit =
                mysqli_fetch_assoc($result);
        }
    }
}


/*
=========================================================
GET ALL EMPLOYEES
=========================================================
*/

$sql =
    "SELECT * FROM employee ORDER BY id DESC";

$result =
    mysqli_query($conn, $sql);

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Employee Payment CRUD</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f2f4f7;
}

.container {

    width: 95%;

    max-width: 1300px;

    margin: 30px auto;
}

h1 {

    text-align: center;

    color: #1d3557;
}

.card {

    background: white;

    padding: 25px;

    margin-bottom: 25px;

    border-radius: 10px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
}

.form-grid {

    display: grid;

    grid-template-columns:
    repeat(auto-fit, minmax(200px, 1fr));

    gap: 15px;
}

.form-group {

    display: flex;

    flex-direction: column;
}

label {

    font-weight: bold;

    margin-bottom: 6px;
}

input {

    padding: 10px;

    border: 1px solid #aaa;

    border-radius: 5px;

    font-size: 15px;
}

button,
.btn {

    padding: 9px 16px;

    border: none;

    border-radius: 5px;

    cursor: pointer;

    text-decoration: none;

    display: inline-block;

    font-size: 14px;
}

.save {

    background: #198754;

    color: white;

    margin-top: 20px;
}

.cancel {

    background: #6c757d;

    color: white;

    margin-top: 20px;
}

.edit {

    background: #0d6efd;

    color: white;
}

.delete {

    background: #dc3545;

    color: white;
}

.table-container {

    overflow-x: auto;
}

table {

    width: 100%;

    min-width: 1200px;

    border-collapse: collapse;
}

th,
td {

    border: 1px solid #ddd;

    padding: 10px;

    text-align: center;
}

th {

    background: #1d3557;

    color: white;
}

tr:nth-child(even) {

    background: #f8f9fa;
}

.total {

    font-weight: bold;

    color: green;
}

.note {

    background: #fff3cd;

    padding: 12px;

    margin-bottom: 20px;

    border-radius: 5px;
}

.message {

    background: #d1e7dd;

    color: #0f5132;

    padding: 12px;

    margin-bottom: 20px;

    border-radius: 5px;

    font-weight: bold;
}

.action-cell {

    white-space: nowrap;
}

.delete-form {

    display: inline;
}

</style>

</head>


<body>


<div class="container">


<h1>Employee Payment CRUD</h1>


<?php

if ($message != "") {

    echo '<div class="message">';

    echo htmlspecialchars($message);

    echo '</div>';
}

?>


<div class="card">


<div class="note">

<strong>Calculation:</strong>

<br>

DA Amount =
Basic Pay × DA % / 100

<br>

HRA Amount =
Basic Pay × HRA % / 100

<br>

Total Payment =
Basic Pay+DA Amount + HRA Amount - PF Deduction + Other Allowance

</div>


<!-- =====================================================
     ADD / UPDATE FORM
====================================================== -->


<form method="POST" action="/">


<input
    type="hidden"
    name="id"
    value="<?php

        if ($edit) {

            echo intval($edit["id"]);
        }

    ?>"
>


<div class="form-grid">


<!-- EMPLOYEE NAME -->

<div class="form-group">

<label>Employee Name</label>

<input
    type="text"
    name="employee_name"
    maxlength="100"
    required

    value="<?php

        if ($edit) {

            echo htmlspecialchars(
                $edit["Employee_name"],
                ENT_QUOTES
            );
        }

    ?>"
>

</div>


<!-- BASIC PAY -->

<div class="form-group">

<label>Basic Pay</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="basic_pay"
    required

    value="<?php

        if ($edit) {

            echo $edit["BASIC_PAY"];
        }

    ?>"
>

</div>


<!-- DA -->

<div class="form-group">

<label>DA %</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="da_percent"
    required

    value="<?php

        if ($edit) {

            echo $edit["DA_PERCENT"];
        }

    ?>"
>

</div>


<!-- HRA -->

<div class="form-group">

<label>HRA %</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="hra_percent"
    required

    value="<?php

        if ($edit) {

            echo $edit["HRA_PERCENT"];
        }

    ?>"
>

</div>


<!-- PF -->

<div class="form-group">

<label>PF Deduction</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="pf_deduction"
    required

    value="<?php

        if ($edit) {

            echo $edit["PF_DEDUCTION"];

        } else {

            echo "0";
        }

    ?>"
>

</div>


<!-- OTHER ALLOWANCE -->

<div class="form-group">

<label>Any Other Allowance</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="other_allowance"
    required

    value="<?php

        if ($edit) {

            echo $edit["ANY_OTHER_ALLOWANCE"];

        } else {

            echo "0";
        }

    ?>"
>

</div>


</div>


<?php

if ($edit) {

?>


<button
    type="submit"
    name="save"
    class="btn save"
>

Update Employee

</button>


<!-- IMPORTANT: root URL -->

<a
    href="/"
    class="btn cancel"
>

Cancel

</a>


<?php

} else {

?>


<button
    type="submit"
    name="save"
    class="btn save"
>

Add Employee

</button>


<?php

}

?>


</form>


</div>


<!-- =====================================================
     EMPLOYEE TABLE
====================================================== -->


<div class="card">


<h2>Employee Records</h2>


<div class="table-container">


<table>


<thead>

<tr>

<th>Employee Name</th>

<th>ID</th>

<th>Basic Pay</th>

<th>DA %</th>

<th>DA Amount</th>

<th>HRA %</th>

<th>HRA Amount</th>

<th>PF Deduction</th>

<th>Other Allowance</th>

<th>Total Payment</th>

<th>Action</th>

</tr>

</thead>


<tbody>


<?php

if (
    $result
    &&
    mysqli_num_rows($result) > 0
) {


    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {

?>


<tr>


<td>

<?php

echo htmlspecialchars(
    $row["Employee_name"]
);

?>

</td>


<td>

<?php

echo intval($row["id"]);

?>

</td>


<td>

<?php

echo number_format(
    $row["BASIC_PAY"],
    2
);

?>

</td>


<td>

<?php

echo number_format(
    $row["DA_PERCENT"],
    2
);

?> %

</td>


<td>

<?php

echo number_format(
    $row["DA_AMOUNT"],
    2
);

?>

</td>


<td>

<?php

echo number_format(
    $row["HRA_PERCENT"],
    2
);

?> %

</td>


<td>

<?php

echo number_format(
    $row["HRA_AMOUNT"],
    2
);

?>

</td>


<td>

<?php

echo number_format(
    $row["PF_DEDUCTION"],
    2
);

?>

</td>


<td>

<?php

echo number_format(
    $row["ANY_OTHER_ALLOWANCE"],
    2
);

?>

</td>


<td class="total">

<?php

echo number_format(
    $row["TOTAL_PAYMENT"],
    2
);

?>

</td>


<td class="action-cell">


<!-- =================================================
     EDIT BUTTON

     IMPORTANT:
     USE ROOT URL /?edit=
================================================== -->


<a
    href="/?edit=<?php
        echo intval($row["id"]);
    ?>"
    class="btn edit"
>

Edit

</a>


<!-- =================================================
     DELETE BUTTON

     IMPORTANT:
     POST TO ROOT /
================================================== -->


<form
    method="POST"
    action="/"
    class="delete-form"
>


<input
    type="hidden"
    name="delete_id"
    value="<?php
        echo intval($row["id"]);
    ?>"
>


<button
    type="submit"
    class="btn delete"

    onclick="
        return confirm(
            'Are you sure you want to delete this employee record?'
        );
    "
>

Delete

</button>


</form>


</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td colspan="11">

No employee records found.

</td>

</tr>


<?php

}

?>


</tbody>

</table>

</div>

</div>


</div>


</body>

</html>


<?php

mysqli_close($conn);

?>
