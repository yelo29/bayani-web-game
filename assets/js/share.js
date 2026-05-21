// Share Card Generation using Canvas API

function generateShareCard(score, total, categoryName, playerName) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Canvas dimensions
    canvas.width = 800;
    canvas.height = 600;
    
    // Background gradient (Philippine flag inspired)
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0, '#0038A8');
    gradient.addColorStop(0.5, '#FFFFFF');
    gradient.addColorStop(1, '#CE1126');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Decorative border (baybayin-inspired pattern)
    ctx.strokeStyle = '#FCD116';
    ctx.lineWidth = 4;
    ctx.strokeRect(20, 20, canvas.width - 40, canvas.height - 40);
    
    // Inner decorative corners
    const cornerSize = 30;
    ctx.strokeStyle = '#FCD116';
    ctx.lineWidth = 3;
    
    // Top-left corner
    ctx.beginPath();
    ctx.moveTo(20, 20 + cornerSize);
    ctx.lineTo(20, 20);
    ctx.lineTo(20 + cornerSize, 20);
    ctx.stroke();
    
    // Top-right corner
    ctx.beginPath();
    ctx.moveTo(canvas.width - 20 - cornerSize, 20);
    ctx.lineTo(canvas.width - 20, 20);
    ctx.lineTo(canvas.width - 20, 20 + cornerSize);
    ctx.stroke();
    
    // Bottom-left corner
    ctx.beginPath();
    ctx.moveTo(20, canvas.height - 20 - cornerSize);
    ctx.lineTo(20, canvas.height - 20);
    ctx.lineTo(20 + cornerSize, canvas.height - 20);
    ctx.stroke();
    
    // Bottom-right corner
    ctx.beginPath();
    ctx.moveTo(canvas.width - 20 - cornerSize, canvas.height - 20);
    ctx.lineTo(canvas.width - 20, canvas.height - 20);
    ctx.lineTo(canvas.width - 20, canvas.height - 20 - cornerSize);
    ctx.stroke();
    
    // Decorative sun rays (inspired by Philippine flag sun)
    const centerX = canvas.width / 2;
    const centerY = 120;
    const rayCount = 8;
    const rayLength = 40;
    
    ctx.fillStyle = '#FCD116';
    for (let i = 0; i < rayCount; i++) {
        const angle = (i * (360 / rayCount)) * Math.PI / 180;
        const x = centerX + Math.cos(angle) * rayLength;
        const y = centerY + Math.sin(angle) * rayLength;
        
        ctx.beginPath();
        ctx.arc(x, y, 6, 0, Math.PI * 2);
        ctx.fill();
    }
    
    // Center sun
    ctx.beginPath();
    ctx.arc(centerX, centerY, 15, 0, Math.PI * 2);
    ctx.fill();
    
    // Title
    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 48px Playfair Display';
    ctx.textAlign = 'center';
    ctx.fillText('Bayani Quiz', canvas.width / 2, 80);
    
    // Score
    ctx.fillStyle = '#FCD116';
    ctx.font = 'bold 72px Poppins';
    ctx.fillText(`${score}/${total}`, canvas.width / 2, 220);
    
    // Category
    ctx.fillStyle = '#0038A8';
    ctx.font = '32px Poppins';
    ctx.fillText(`Category: ${categoryName}`, canvas.width / 2, 300);
    
    // Player Name
    ctx.fillStyle = '#CE1126';
    ctx.font = '28px Poppins';
    ctx.fillText(`Player: ${playerName}`, canvas.width / 2, 360);
    
    // Decorative divider line
    ctx.strokeStyle = '#FCD116';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(200, 420);
    ctx.lineTo(600, 420);
    ctx.stroke();
    
    // Website URL
    ctx.fillStyle = '#0038A8';
    ctx.font = '24px Poppins';
    ctx.fillText('bayani-quiz.com', canvas.width / 2, 480);
    
    // Tagline
    ctx.fillStyle = '#666666';
    ctx.font = '18px Poppins';
    ctx.fillText('Test Your Knowledge of Philippine History', canvas.width / 2, 520);
    
    return canvas.toDataURL('image/png');
}

function downloadCard(score, total, categoryName, playerName) {
    const dataUrl = generateShareCard(score, total, categoryName, playerName);
    
    const link = document.createElement('a');
    link.download = 'bayani-quiz-score.png';
    link.href = dataUrl;
    link.click();
}

function shareOnFacebook(score, total, categoryName, playerName) {
    const text = `I scored ${score}/${total} in Bayani Quiz - ${categoryName}! Test your knowledge of Philippine history!`;
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${encodeURIComponent(text)}`, '_blank');
}
