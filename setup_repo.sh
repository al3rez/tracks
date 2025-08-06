#!/bin/bash

# Create GitHub repository and push Tracks framework
echo "Setting up Tracks framework repository..."

# Initialize git if not already initialized
if [ ! -d ".git" ]; then
    git init
fi

# Add all files
git add -A

# Create initial commit
git commit -m "Initial commit: Tracks PHP framework - Rails-like MVC framework for PHP

- Implemented core MVC architecture with Application, Router, Controller, and Model classes
- Created ActiveRecord ORM with query builder, validations, and callbacks  
- Added Rails-style routing with RESTful resources support
- Implemented view system with layouts and partials
- Created database migration system
- Added generators for models, controllers, and scaffolding
- Implemented CLI commands (server, console, migrate, generate)
- Created 'tracks new' command for generating new applications
- Fixed PSR-4 autoloading with proper directory capitalization
- Added configuration system for application, database, and routes
- Included helper functions for common tasks"

# Create the GitHub repository using gh CLI
echo "Creating GitHub repository..."
gh repo create al3rez/tracks --public --description "A Ruby on Rails-like PHP framework" --source=. --remote=origin --push

# If gh CLI is not available, use these manual commands instead:
# git remote add origin https://github.com/al3rez/tracks.git
# git branch -M main  
# git push -u origin main

echo "Repository created and pushed to https://github.com/al3rez/tracks"