<?php
require_once __DIR__ . '/init.php';
include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Calorie Tracker</title>

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#f4f6f9;
}

/* Banner */
.banner{
    height:160px;
    background:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),
    url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1200&q=80');
    background-size:cover;
    background-position:center;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:28px;
    font-weight:bold;
}

/* Container */
.container{
    max-width:900px;
    margin:40px auto;
    padding:0 20px;
}

/* Card */
.card{
    background:#fff;
    border-radius:15px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

/* Inputs */
.input-group{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

input{
    flex:1;
    padding:12px;
    border-radius:8px;
    border:1px solid #ddd;
}

/* Button */
.btn{
    background:#e67e22;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    cursor:pointer;
}

/* List */
.list{
    margin-top:20px;
}

.item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #eee;
}

.delete{
    color:red;
    cursor:pointer;
}

/* Total */
.total{
    margin-top:20px;
    font-size:20px;
    font-weight:bold;
    color:#27ae60;
}
</style>
</head>

<body>

<div class="banner">Calorie Tracking</div>

<div class="container">

    <div class="card">
        <h3>Add Your Meal</h3>

        <div class="input-group">
            <input type="text" id="food" placeholder="Food name">
            <input type="number" id="calories" placeholder="Calories">
            <button class="btn" onclick="addItem()">Add</button>
        </div>
    </div>

    <div class="card">
        <h3>Your Daily Meals</h3>
        <div id="list" class="list"></div>

        <div class="total">
            Total Calories: <span id="total">0</span> kcal
        </div>
    </div>

</div>

<script>
let total = 0;

function addItem(){
    let food = document.getElementById("food").value;
    let cal = parseInt(document.getElementById("calories").value);

    if(!food || !cal) return;

    let list = document.getElementById("list");

    let item = document.createElement("div");
    item.className = "item";

    item.innerHTML = `
        <span>${food} - ${cal} kcal</span>
        <span class="delete" onclick="removeItem(this, ${cal})">❌</span>
    `;

    list.appendChild(item);

    total += cal;
    document.getElementById("total").innerText = total;

    document.getElementById("food").value = "";
    document.getElementById("calories").value = "";
}

function removeItem(el, cal){
    el.parentElement.remove();
    total -= cal;
    document.getElementById("total").innerText = total;
}
</script>

</body>
</html>