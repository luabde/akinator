<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Akinator PHP</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: #fff;
      padding: 2rem 3rem;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      text-align: center;
    }
    button {
      background: #0dff00ff;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      transition: background 0.3s;
    }
    button:hover {
      background: #ff7300ff;
    }
    select {
      padding: 0.5rem;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🤔 Akinator PHP</h1>
    <p>Piensa en un personaje... ¡yo intentaré adivinar quién es!</p>
    <form action="juego.php" method="POST">
      <button type="submit">Comenzar</button>
    </form>
  </div>
</body>
</html>
