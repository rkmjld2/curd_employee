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

    $total_payment =
		 $basic_pay
        +$da_amount
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

        $edit_result =
            mysqli_query($conn, $sql);

        if ($edit_result) {

            $edit =
                mysqli_fetch_assoc($edit_result);
        }
    }
}


/*
=========================================================
SQL SELECT SEARCH
=========================================================

The search box accepts MySQL SELECT commands.

Examples:

SELECT * FROM employee WHERE id = 1;

SELECT * FROM employee
WHERE Employee_name LIKE '%Ravi%';

SELECT * FROM employee
WHERE id BETWEEN 1 AND 10;

SELECT * FROM employee
WHERE BASIC_PAY > 30000
ORDER BY BASIC_PAY DESC;

Only SELECT statements are permitted.

=========================================================
*/

$search_sql = "";

$search_result = NULL;

$search_error = "";

$search_count = 0;


if (
    isset($_GET["search"])
) {

    $search_sql =
        trim($_GET["search"]);


    if ($search_sql !== "") {

        /*
        -------------------------------------------------
        REMOVE ONE OPTIONAL SEMICOLON AT END
        -------------------------------------------------
        */

        $search_sql =
            rtrim($search_sql);

        $search_sql =
            rtrim($search_sql, ";");

        $search_sql =
            trim($search_sql);


        /*
        -------------------------------------------------
        CHECK THAT COMMAND STARTS WITH SELECT
        -------------------------------------------------
        */

        if (
            !preg_match(
                '/^SELECT\s/i',
                $search_sql
            )
        ) {

            $search_error =
                "Only SELECT statements are allowed.";

        }


        /*
        -------------------------------------------------
        BLOCK DANGEROUS SQL KEYWORDS
        -------------------------------------------------
        */

        elseif (
            preg_match(
                '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|RENAME|REPLACE|GRANT|REVOKE|CALL|LOAD|SET|USE)\b/i',
                $search_sql
            )
        ) {

            $search_error =
                "Only SELECT statements are allowed. "
                . "INSERT, UPDATE, DELETE, DROP, ALTER, "
                . "TRUNCATE and other modification commands "
                . "are not permitted.";

        }


        /*
        -------------------------------------------------
        BLOCK MULTIPLE SQL STATEMENTS
        -------------------------------------------------
        */

        elseif (
            strpos(
                $search_sql,
                ";"
            ) !== false
        ) {

            $search_error =
                "Please enter only one SELECT statement.";

        }


        /*
        -------------------------------------------------
        EXECUTE SELECT
        -------------------------------------------------
        */

        else {

            $search_result =
                mysqli_query(
                    $conn,
                    $search_sql
                );


            if (!$search_result) {

                $search_error =
                    "SQL Error: "
                    . mysqli_error($conn);

            } else {

                $search_count =
                    mysqli_num_rows(
                        $search_result
                    );
            }
        }
    }
}


/*
=========================================================
NORMAL EMPLOYEE LIST
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

    max-width: 1400px;

    margin: 30px auto;
}


h1 {

    text-align: center;

    color: #1d3557;

    margin-bottom: 25px;
}


h2 {

    color: #1d3557;
}


.card {

    background: white;

    padding: 25px;

    margin-bottom: 25px;

    border-radius: 10px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.12);
}


/*
=========================================================
SEARCH
=========================================================
*/

.search-box {

    width: 100%;
}


.search-box textarea {

    width: 100%;

    min-height: 90px;

    padding: 12px;

    border: 1px solid #999;

    border-radius: 6px;

    font-family: Consolas, monospace;

    font-size: 15px;

    resize: vertical;
}


.search-buttons {

    margin-top: 10px;

    display: flex;

    gap: 10px;

    flex-wrap: wrap;
}


.search-button {

    background: #6f42c1;

    color: white;
}


.clear-button {

    background: #6c757d;

    color: white;
}


.search-info {

    background: #e7f1ff;

    padding: 12px;

    margin-top: 15px;

    margin-bottom: 15px;

    border-radius: 5px;

    color: #084298;
}


.search-error {

    background: #f8d7da;

    color: #842029;

    padding: 12px;

    margin-top: 15px;

    margin-bottom: 15px;

    border-radius: 5px;

    font-weight: bold;
}


.search-help {

    background: #fff3cd;

    padding: 15px;

    margin-top: 15px;

    border-radius: 5px;

    line-height: 1.7;
}


.search-help code {

    background: #eee;

    padding: 3px 6px;

    border-radius: 4px;

    font-family: Consolas, monospace;
}


/*
=========================================================
FORM
=========================================================
*/

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

    min-width: 1250px;

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

    line-height: 1.7;
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

/*
=========================================================
MESSAGE
=========================================================
*/

if ($message != "") {

    echo '<div class="message">';

    echo htmlspecialchars($message);

    echo '</div>';
}

?>


<!-- =====================================================
     SQL SELECT SEARCH
====================================================== -->

<div class="card">


<h2>SQL SELECT Search</h2>


<div class="search-box">


<form
    method="GET"
    action="/"
>


<textarea
    name="search"
    placeholder="Enter MySQL SELECT command here, for example: SELECT * FROM employee WHERE id = 1;"
><?php

echo htmlspecialchars(
    $search_sql,
    ENT_QUOTES
);

?></textarea>


<div class="search-buttons">


<button
    type="submit"
    class="btn search-button"
>

Search

</button>


<a
    href="/"
    class="btn clear-button"
>

Show All

</a>


</div>


</form>


</div>


<div class="search-help">

<strong>Examples:</strong>

<br><br>


1. Search by ID:

<br>

<code>
SELECT * FROM employee WHERE id = 1;
</code>


<br><br>


2. Search by employee name:

<br>

<code>
SELECT * FROM employee
WHERE Employee_name LIKE '%Ravi%';
</code>


<br><br>


3. Search IDs from 1 to 10:

<br>

<code>
SELECT * FROM employee
WHERE id BETWEEN 1 AND 10;
</code>


<br><br>


4. Basic Pay greater than 30000:

<br>

<code>
SELECT * FROM employee
WHERE BASIC_PAY > 30000;
</code>


<br><br>


5. Basic Pay between 30000 and 50000:

<br>

<code>
SELECT * FROM employee
WHERE BASIC_PAY BETWEEN 30000 AND 50000;
</code>


<br><br>


6. Highest Total Payment first:

<br>

<code>
SELECT * FROM employee
ORDER BY TOTAL_PAYMENT DESC;
</code>


<br><br>


7. Select particular columns:

<br>

<code>
SELECT Employee_name, BASIC_PAY, TOTAL_PAYMENT
FROM employee
WHERE TOTAL_PAYMENT > 40000;
</code>


<br><br>


<strong>
Only SELECT commands are permitted in this search box.
</strong>

</div>


<?php

/*
=========================================================
SEARCH ERROR
=========================================================
*/

if ($search_error != "") {

?>


<div class="search-error">

<?php

echo htmlspecialchars(
    $search_error
);

?>

</div>


<?php

}


/*
=========================================================
SEARCH RESULT
=========================================================
*/

if (
    $search_sql !== ""
    &&
    $search_error === ""
    &&
    $search_result
) {

?>


<div class="search-info">

<strong>
Search completed.
</strong>

&nbsp;&nbsp;

Records found:

<strong>

<?php

echo $search_count;

?>

</strong>

</div>


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

if ($search_count > 0) {


    while (
        $row =
        mysqli_fetch_assoc(
            $search_result
        )
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

echo isset($row["id"])
    ? intval($row["id"])
    : "";

?>

</td>


<td>

<?php

echo isset($row["BASIC_PAY"])
    ? number_format(
        $row["BASIC_PAY"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["DA_PERCENT"])
    ? number_format(
        $row["DA_PERCENT"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["DA_AMOUNT"])
    ? number_format(
        $row["DA_AMOUNT"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["HRA_PERCENT"])
    ? number_format(
        $row["HRA_PERCENT"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["HRA_AMOUNT"])
    ? number_format(
        $row["HRA_AMOUNT"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["PF_DEDUCTION"])
    ? number_format(
        $row["PF_DEDUCTION"],
        2
    )
    : "";

?>

</td>


<td>

<?php

echo isset($row["ANY_OTHER_ALLOWANCE"])
    ? number_format(
        $row["ANY_OTHER_ALLOWANCE"],
        2
    )
    : "";

?>

</td>


<td class="total">

<?php

echo isset($row["TOTAL_PAYMENT"])
    ? number_format(
        $row["TOTAL_PAYMENT"],
        2
    )
    : "";

?>

</td>


<td class="action-cell">


<?php

/*
---------------------------------------------------------
Only provide Edit/Delete if ID is present.
---------------------------------------------------------
*/

if (isset($row["id"])) {

?>


<a
    href="/?edit=<?php
        echo intval($row["id"]);
    ?>"
    class="btn edit"
>

Edit

</a>


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


<?php

}

?>


</td>


</tr>


<?php

    }

}

else {

?>


<tr>

<td colspan="11">

No records found.

</td>

</tr>


<?php

}

?>


</tbody>

</table>

</div>


<?php

}

?>


</div>


<!-- =====================================================
     ADD / UPDATE EMPLOYEE
====================================================== -->


<div class="card">


<h2>

<?php

if ($edit) {

    echo "Edit Employee";

} else {

    echo "Add Employee";
}

?>

</h2>


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
Basic Pay + DA Amount + HRA Amount
- PF Deduction + Other Allowance

</div>


<form
    method="POST"
    action="/"
>


<input
    type="hidden"
    name="id"

    value="<?php

        if ($edit) {

            echo intval(
                $edit["id"]
            );
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
     ALL EMPLOYEE RECORDS
====================================================== -->


<div class="card">


<h2>All Employee Records</h2>


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

echo intval(
    $row["id"]
);

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


<a
    href="/?edit=<?php
        echo intval($row["id"]);
    ?>"
    class="btn edit"
>

Edit

</a>


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

}

else {

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
