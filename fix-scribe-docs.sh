#!/bin/bash
# Script to add custom CSS to hide Endpoints section after Scribe generation

SCRIBE_VIEW="resources/views/scribe/index.blade.php"

# Check if the view file exists
if [ ! -f "$SCRIBE_VIEW" ]; then
    echo "❌ Scribe view not found. Please run: php artisan scribe:generate first"
    exit 1
fi

# Check if CSS is already added
if grep -q "Hide undocumented endpoints" "$SCRIBE_VIEW"; then
    echo "✅ Custom CSS already exists in $SCRIBE_VIEW"
    exit 0
fi

# Add custom CSS after the language-style tag
sed -i '' '/<\/style>/a\
\
    <style>\
        /* Hide undocumented endpoints (those without @group annotation) */\
        #tocify-header-undocumented,\
        #tocify-subheader-undocumented,\
        #undocumented,\
        [id^="undocumented-"],\
        #tocify-header-endpoints,\
        #tocify-subheader-endpoints,\
        #endpoints,\
        [id^="endpoints-"] {\
            display: none !important;\
        }\
    </style>
' "$SCRIBE_VIEW"

echo "✅ Successfully added custom CSS to hide undocumented endpoints and default Endpoints group!"
echo "📄 File modified: $SCRIBE_VIEW"
