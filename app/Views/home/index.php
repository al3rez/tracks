<div style="text-align: center; padding: 3rem 0;">
    <h1 style="font-size: 3rem; margin-bottom: 1rem; color: #c52f24;">
        <?= $title ?>
    </h1>
    
    <p style="font-size: 1.25rem; color: #666; margin-bottom: 2rem;">
        <?= $message ?>
    </p>
    
    <div style="background: #f8f9fa; padding: 2rem; border-radius: 8px; max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 1rem;">Getting Started</h2>
        
        <p style="margin-bottom: 1rem;">
            Tracks brings the elegance and productivity of Ruby on Rails to PHP.
        </p>
        
        <h3 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Quick Commands:</h3>
        
        <pre style="background: #333; color: #fff; padding: 1rem; border-radius: 4px; text-align: left; overflow-x: auto;">
# Start development server
php tracks server

# Generate a scaffold
php tracks generate:scaffold post title:string content:text

# Run migrations
php tracks db:migrate

# Start console
php tracks console

# View all routes
php tracks routes
        </pre>
        
        <h3 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Features:</h3>
        
        <ul style="text-align: left; max-width: 400px; margin: 0 auto; line-height: 2;">
            <li>✓ MVC Architecture</li>
            <li>✓ ActiveRecord-style ORM</li>
            <li>✓ RESTful Routing</li>
            <li>✓ Database Migrations</li>
            <li>✓ Scaffolding Generators</li>
            <li>✓ Interactive Console</li>
            <li>✓ Convention over Configuration</li>
        </ul>
    </div>
</div>