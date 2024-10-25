<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskMaster - Your Personal To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .landing-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
            padding: 3rem;
            margin: 2rem;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .btn-custom-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom-secondary {
            background-color: var(--success-color);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom-primary:hover,
        .btn-custom-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }

        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.5;
            animation: float 15s infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(100px, 100px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape" style="top: 20%; left: 10%; background: #4e73df33; width: 60px; height: 60px; border-radius: 30% 70% 70% 30%/30% 30% 70% 70%;"></div>
        <div class="shape" style="top: 60%; left: 80%; background: #1cc88a33; width: 80px; height: 80px; border-radius: 50%;"></div>
        <div class="shape" style="top: 80%; left: 30%; background: #36b9cc33; width: 70px; height: 70px; border-radius: 50% 20% 30% 70%;"></div>
    </div>

    <div class="landing-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-section">
                        <h1 class="hero-title">TaskMaster</h1>
                        <p class="hero-subtitle">Organize your life, achieve your goals, and boost your productivity with our intuitive task management system.</p>
                        <div class="d-flex gap-3">
                            <a href="login.php" class="btn btn-custom-secondary">Get Started</a>
                            <a href="register.php" class="btn btn-custom-primary">Create Account</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-card">
                                <i class="fas fa-tasks feature-icon"></i>
                                <h3>Task Management</h3>
                                <p>Easily create, organize, and track your tasks in one place.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <i class="fas fa-calendar-check feature-icon"></i>
                                <h3>Due Dates</h3>
                                <p>Never miss a deadline with our reminder system.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <i class="fas fa-mobile-alt feature-icon"></i>
                                <h3>Mobile Friendly</h3>
                                <p>Access your tasks anywhere, anytime on any device.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <i class="fas fa-chart-line feature-icon"></i>
                                <h3>Progress Tracking</h3>
                                <p>Monitor your productivity with visual progress indicators.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>