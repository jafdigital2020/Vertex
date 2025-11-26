#!/bin/bash

# Generate Scribe documentation
echo "Generating API documentation..."
php -d memory_limit=512M artisan scribe:generate

# Remove the undocumented "Endpoints" group
echo "Removing undocumented endpoints..."

# Delete the YAML file
if [ -f ".scribe/endpoints/00.yaml" ]; then
    if grep -q "name: Endpoints" .scribe/endpoints/00.yaml; then
        rm .scribe/endpoints/00.yaml
        echo "✓ Removed undocumented endpoints YAML"
    fi
fi

# Clean up the generated HTML to remove undocumented endpoints
php cleanup-docs.php

echo ""
echo "✓ Documentation generated successfully!"
echo "View it at: http://localhost:8000/docs"
