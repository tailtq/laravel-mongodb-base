function Circle(x, y, radius, color) {
    this.x = x;
    this.y = y;
    this.radius = radius;
    this.color = color;
}

Circle.prototype.draw = function (ctx) {
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.closePath();
    ctx.fillStyle = this.color;
    ctx.fill();
};

Circle.prototype.drag = function (x, y) {
    this.x = x;
    this.y = y;
};
