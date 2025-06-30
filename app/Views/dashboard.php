<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }

        .header {
            background:rgb(2, 6, 83);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0px 10px;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .box {
            background: white;
            text-align: center;
            color:darkblue;
            text-shadow: #2d98da;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .table {
            max-width: 80%;
            background: white;
            color:darkblue;
            text-shadow: #2d98da;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #2d98da;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1c7ed6;
        }

        .logout {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
  <?php   $data=session()->get('user');   ?>

    <div class="header">
        <div class="user-info">
            <img src="<?= $data['picture']?>" alt="Profile Photo">
            <span><?= $data['email'] ?></span>            
        </div>
        <a href="<?= base_url('logout') ?>" class="logout">Logout</a>
    </div>
    <div class="box">
        <h1>Welcome, <?= $data['name'] ?></h1>
    </div>
    
    <div class="container">        
        <h2>Set Phone Number</h2>
        <form action="<?= base_url('home/updatePhone') ?>" method="post">
            <?= csrf_field() ?>
            <input type="text" name="phone" placeholder="Enter phone number" value="<?= esc($user['phone'] ?? '') ?>" required>
            <button type="submit">Update</button>
        </form>
        <?php if (session()->getFlashdata('message')): ?>
        <p style="color: green;"><?= session()->getFlashdata('message') ?></p>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <p style="color: red;"><?= session()->getFlashdata('error') ?></p>
        <?php endif; ?>
    </div>   

    <h2 style="text-align: center";>Upcoming Events</h2>
    <div class="table" >
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <th style="text-align:left; border-bottom:1px solid #ccc; padding:8px;">Event</th>
            <th style="text-align:left; border-bottom:1px solid #ccc; padding:8px;">Start Time</th>
        </tr>

        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): 
                $start = $event->getStart()->getDateTime() ?: $event->getStart()->getDate(); ?>
                <tr>
                    <td style="padding:8px;"><?= esc($event->getSummary()) ?></td>
                    <td style="padding:8px;"><?= date('d M Y, h:i A', strtotime($start)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2" style="padding:8px;">No upcoming events found.</td></tr>
        <?php endif; ?>
    </table>
    </div>
</body>
</html>

