<?php
session_start();

// INIT GAME
if (!isset($_SESSION['plant'])) {
    $_SESSION['plant'] = [
        'water' => 50,
        'sun' => 50,
        'growth' => 0,
        'health' => 100,
        'coin' => 50,
        'pot' => 1,
        'day' => 1
    ];
}

$plant = &$_SESSION['plant'];
$message = "";

function clamp(&$value, $min = 0, $max = 100) {
    $value = max($min, min($max, $value));
}

// PERIKSA JIKA ADA ACTION RESET
if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    session_destroy();
    header("Location: " . basename($_SERVER['PHP_SELF']));
    exit;
}

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'water':
            $plant['water'] += 15;
            $message = "Tanaman disiram! +15 Air";
            break;
        case 'sun':
            $plant['sun'] += 15;
            $growthIncrease = 8 * $plant['pot'];
            $plant['growth'] += $growthIncrease;
            $message = "Tanaman dijemur! +15 Cahaya, +" . $growthIncrease . " Pertumbuhan";
            break;
        case 'fertilize':
            if ($plant['coin'] >= 20) {
                $plant['coin'] -= 20;
                $plant['growth'] += 25;
                $message = "Pupuk berhasil digunakan! +25 Pertumbuhan";
            } else {
                $message = "❌ Coin tidak cukup!";
            }
            break;
        case 'harvest':
            if ($plant['growth'] >= 100) {
                $coinEarned = 50 * $plant['pot'];
                $plant['growth'] = 0;
                $plant['coin'] += $coinEarned;
                $plant['day']++;
                $message = "🎉 Panen berhasil! +" . $coinEarned . " Coin";
            } else {
                $message = "❌ Belum bisa dipanen! Pertumbuhan: " . $plant['growth'] . "%";
            }
            break;
        case 'upgrade':
            if ($plant['coin'] >= 100) {
                $plant['coin'] -= 100;
                $plant['pot']++;
                $message = "⚡ Pot di-upgrade ke level " . $plant['pot'] . "!";
            } else {
                $message = "❌ Coin tidak cukup! Butuh 100 coin, kamu punya: " . $plant['coin'];
            }
            break;
    }

    // SIMULASI WAKTU (hanya jika aksi valid dan bukan reset)
    if (in_array($_GET['action'], ['water', 'sun', 'fertilize', 'harvest', 'upgrade'])) {
        $plant['water'] -= rand(3, 8);
        $plant['sun'] -= rand(3, 8);
        
        // HEALTH RECOVERY / DAMAGE
        if ($plant['water'] >= 40 && $plant['water'] <= 80 && 
            $plant['sun'] >= 40 && $plant['sun'] <= 80) {
            $plant['health'] += 2; // Recovery jika kondisi ideal
        }

        // OVERCARE DAMAGE
        if ($plant['water'] > 100 || $plant['sun'] > 100) {
            $plant['health'] -= 15;
            $message = "⚠️ Tanaman stress karena perawatan berlebihan! -15 Kesehatan";
        }
        
        // UNDER CARE DAMAGE
        if ($plant['water'] < 20 || $plant['sun'] < 20) {
            $plant['health'] -= 10;
            $message = "⚠️ Tanaman kekurangan perawatan! -10 Kesehatan";
        }

        clamp($plant['water']);
        clamp($plant['sun']);
        clamp($plant['growth']);
        clamp($plant['health']);
    }
}

// Tentukan gambar tanaman berdasarkan growth
$plantImage = "🌱"; // Seed
if ($plant['growth'] >= 25) $plantImage = "🌿";
if ($plant['growth'] >= 50) $plantImage = "🪴";
if ($plant['growth'] >= 75) $plantImage = "🌳";
if ($plant['growth'] >= 100) $plantImage = "🎄";

// Tentukan warna kesehatan
$healthColor = "#2ecc71"; // Green
if ($plant['health'] <= 70) $healthColor = "#f39c12"; // Orange
if ($plant['health'] <= 40) $healthColor = "#e74c3c"; // Red

// Tentukan status tanaman
$plantStatus = "";
if ($plant['growth'] < 25) $plantStatus = "Benih - Perlu banyak perawatan!";
elseif ($plant['growth'] < 50) $plantStatus = "Kecambah - Mulai tumbuh!";
elseif ($plant['growth'] < 75) $plantStatus = "Tanaman Muda - Semakin besar!";
elseif ($plant['growth'] < 100) $plantStatus = "Tanaman Dewasa - Hampir siap panen!";
else $plantStatus = "SIAP PANEN! 💰";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Plant Care Simulator Game</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #fff;
        }
        
        .game-container {
            width: 800px;
            max-width: 95%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .game-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .game-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #2ecc71, #f1c40f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .game-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .game-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .plant-section {
            flex: 1;
            min-width: 300px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
        }
        
        .plant-display {
            font-size: 120px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            transition: transform 0.3s;
        }
        
        .plant-display:hover {
            transform: scale(1.05);
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        .stats-section {
            flex: 2;
            min-width: 300px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-5px);
        }
        
        .stat-name {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #ddd;
        }
        
        .stat-bar {
            height: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .stat-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .stat-value {
            text-align: right;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .actions-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border: none;
            border-radius: 12px;
            padding: 16px;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            text-align: center;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        .action-btn.fertilize {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }
        
        .action-btn.upgrade {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
        }
        
        .action-btn.harvest {
            background: linear-gradient(45deg, #f39c12, #e67e22);
        }
        
        .action-btn.reset {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .action-btn.reset:hover {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52);
        }
        
        .currency-display {
            display: flex;
            justify-content: space-between;
            background: rgba(0, 0, 0, 0.2);
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: bold;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .currency-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            text-align: center;
            font-size: 1.2rem;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-left: 5px solid #2ecc71;
            animation: fadeIn 0.5s;
        }
        
        .message-box.warning {
            border-left-color: #e74c3c;
        }
        
        .day-counter {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .tips {
            text-align: center;
            margin-top: 25px;
            opacity: 0.8;
            font-size: 0.9rem;
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 10px;
        }
        
        /* Modal Konfirmasi Reset */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 30px;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .modal-btn.confirm {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .modal-btn.cancel {
            background: linear-gradient(45deg, #7f8c8d, #95a5a6);
            color: white;
        }
        
        .modal-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-section {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .game-header h1 {
                font-size: 2rem;
            }
            
            .day-counter {
                position: relative;
                top: 0;
                right: 0;
                margin-bottom: 20px;
                justify-content: center;
            }
            
            .currency-display {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            
            .modal-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .actions-section {
                grid-template-columns: 1fr;
            }
            
            .plant-display {
                font-size: 80px;
                height: 150px;
            }
            
            .game-content {
                flex-direction: column;
            }
        }
        
        .growth-ready {
            border: 3px solid #f1c40f;
            box-shadow: 0 0 20px rgba(241, 196, 15, 0.5);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Modal Konfirmasi Reset -->
    <div class="modal-overlay" id="resetModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 15px; color: #fff;"><i class="fas fa-exclamation-triangle"></i> Reset Game</h2>
            <p style="margin-bottom: 20px; line-height: 1.5;">Apakah Anda yakin ingin mereset game? Semua progress akan hilang dan game akan dimulai dari awal.</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm" onclick="confirmReset()">
                    <i class="fas fa-redo"></i> Ya, Reset Game
                </button>
                <button class="modal-btn cancel" onclick="closeModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>
    
    <div class="game-container">
        <div class="day-counter">
            <i class="fas fa-calendar-day"></i> Hari: <?php echo $plant['day']; ?>
        </div>
        
        <div class="game-header">
            <h1><i class="fas fa-seedling"></i> Plant Care Simulator</h1>
            <p class="subtitle">Rawat tanamanmu, tingkatkan pot, dan kumpulkan koin sebanyak-banyaknya!</p>
        </div>
        
        <div class="currency-display">
            <div class="currency-item">
                <i class="fas fa-coins" style="color: #f1c40f;"></i>
                <span>Koin: <?php echo $plant['coin']; ?></span>
            </div>
            <div class="currency-item">
                <i class="fas fa-flask" style="color: #9b59b6;"></i>
                <span>Level Pot: <?php echo $plant['pot']; ?></span>
            </div>
            <div class="currency-item">
                <i class="fas fa-heart" style="color: <?php echo $healthColor; ?>"></i>
                <span>Kesehatan: <?php echo $plant['health']; ?>%</span>
            </div>
        </div>
        
        <div class="game-content">
            <div class="plant-section">
                <div class="plant-display <?php echo $plant['growth'] >= 100 ? 'pulse growth-ready' : ''; ?>">
                    <?php echo $plantImage; ?>
                </div>
                <h3>Status Tanaman</h3>
                <p><?php echo $plantStatus; ?></p>
            </div>
            
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-name">
                            <i class="fas fa-tint" style="color: #3498db;"></i>
                            <span>Air</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill water" style="width: <?php echo $plant['water']; ?>%; background: #3498db;"></div>
                        </div>
                        <div class="stat-value"><?php echo $plant['water']; ?>%</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-name">
                            <i class="fas fa-sun" style="color: #f1c40f;"></i>
                            <span>Cahaya</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill sun" style="width: <?php echo $plant['sun']; ?>%; background: #f1c40f;"></div>
                        </div>
                        <div class="stat-value"><?php echo $plant['sun']; ?>%</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-name">
                            <i class="fas fa-chart-line" style="color: #2ecc71;"></i>
                            <span>Pertumbuhan</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill growth" style="width: <?php echo $plant['growth']; ?>%; background: #2ecc71;"></div>
                        </div>
                        <div class="stat-value"><?php echo $plant['growth']; ?>%</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-name">
                            <i class="fas fa-heart" style="color: <?php echo $healthColor; ?>"></i>
                            <span>Kesehatan</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill health" style="width: <?php echo $plant['health']; ?>%; background: <?php echo $healthColor; ?>"></div>
                        </div>
                        <div class="stat-value"><?php echo $plant['health']; ?>%</div>
                    </div>
                </div>
                
                <div class="actions-section">
                    <a href="?action=water" class="action-btn">
                        <i class="fas fa-tint"></i> Siram (+15)
                    </a>
                    
                    <a href="?action=sun" class="action-btn">
                        <i class="fas fa-sun"></i> Jemur (+15)
                    </a>
                    
                    <a href="?action=fertilize" class="action-btn fertilize">
                        <i class="fas fa-seedling"></i> Pupuk (20)
                    </a>
                    
                    <a href="?action=upgrade" class="action-btn upgrade">
                        <i class="fas fa-flask"></i> Upgrade Pot (100)
                    </a>
                    
                    <a href="?action=harvest" class="action-btn harvest">
                        <i class="fas fa-harvest"></i> Panen
                    </a>
                    
                    <button class="action-btn reset" onclick="showResetModal()">
                        <i class="fas fa-redo"></i> Restart Game
                    </button>
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="message-box <?php echo strpos($message, '❌') !== false || strpos($message, '⚠️') !== false ? 'warning' : ''; ?>">
            <i class="fas fa-bell"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="tips">
            <p><i class="fas fa-lightbulb"></i> Tips: Jaga air dan cahaya antara 40-80% untuk kesehatan optimal!</p>
            <p>Panen saat pertumbuhan mencapai 100% untuk mendapatkan koin lebih banyak!</p>
            <p>Level pot meningkatkan kecepatan pertumbuhan dan jumlah koin saat panen!</p>
        </div>
    </div>
    
    <script>
        // Modal functions
        function showResetModal() {
            document.getElementById('resetModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('resetModal').style.display = 'none';
        }
        
        function confirmReset() {
            window.location.href = "?action=reset";
        }
        
        // Close modal when clicking outside
        document.getElementById('resetModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Efek sukses visual
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.action-btn:not(.reset)');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Cegah klik berulang cepat
                    if (this.classList.contains('clicked')) {
                        e.preventDefault();
                        return;
                    }
                    
                    this.classList.add('clicked');
                    setTimeout(() => {
                        this.classList.remove('clicked');
                    }, 1000);
                });
            });
            
            // Efek animasi untuk stat bar
            const statFills = document.querySelectorAll('.stat-fill');
            statFills.forEach(fill => {
                const originalWidth = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = originalWidth;
                }, 300);
            });
            
            // Highlight tanaman yang siap panen
            const plantDisplay = document.querySelector('.plant-display');
            if (plantDisplay.classList.contains('growth-ready')) {
                setInterval(() => {
                    plantDisplay.classList.toggle('pulse');
                }, 4000);
            }
        });
    </script>
</body>
</html>