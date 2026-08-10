-- =====================================================
-- EMPLOYEE PAYROLL DATABASE
-- =====================================================

CREATE DATABASE IF NOT EXISTS employeer;

USE employeer;


-- =====================================================
-- EMPLOYEE TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS employee (

    Employee_name VARCHAR(100) NOT NULL DEFAULT '',

    id INT AUTO_INCREMENT PRIMARY KEY,

    BASIC_PAY DECIMAL(12,2) NOT NULL DEFAULT 0,

    DA_PERCENT DECIMAL(8,2) NOT NULL DEFAULT 0,

    DA_AMOUNT DECIMAL(12,2) NOT NULL DEFAULT 0,

    HRA_PERCENT DECIMAL(8,2) NOT NULL DEFAULT 0,

    HRA_AMOUNT DECIMAL(12,2) NOT NULL DEFAULT 0,

    PF_DEDUCTION DECIMAL(12,2) NOT NULL DEFAULT 0,

    ANY_OTHER_ALLOWANCE DECIMAL(12,2) NOT NULL DEFAULT 0,

    TOTAL_PAYMENT DECIMAL(12,2) NOT NULL DEFAULT 0

);
