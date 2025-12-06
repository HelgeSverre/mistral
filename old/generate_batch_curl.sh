#!/bin/bash

BATCH_FILE_ID="92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2"
API_KEY=$(grep MISTRAL_API_KEY .env | cut -d '=' -f2 | tr -d '"')

echo "=== Creating Batch Job ==="
RESPONSE=$(curl -s -X POST https://api.mistral.ai/v1/batch/jobs \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d "{
    \"input_files\": [\"${BATCH_FILE_ID}\"],
    \"model\": \"mistral-small-latest\",
    \"endpoint\": \"/v1/chat/completions\"
  }")

echo "$RESPONSE" | jq .
JOB_ID=$(echo "$RESPONSE" | jq -r '.id // empty')

if [ -n "$JOB_ID" ] && [ "$JOB_ID" != "null" ]; then
    echo ""
    echo "✅ Job created: $JOB_ID"

    # Save create fixture
    echo "$RESPONSE" | jq -R -s '{statusCode: 201, headers: {"Content-Type": "application/json"}, data: ., context: []}' > tests/Fixtures/Saloon/batch/create.json
    sed -i '' 's/\\n//g' tests/Fixtures/Saloon/batch/create.json

    echo "✅ Saved: tests/Fixtures/Saloon/batch/create.json"
    echo ""

    # Get batch job
    echo "=== Getting Batch Job Details ==="
    sleep 2
    GET_RESPONSE=$(curl -s -X GET "https://api.mistral.ai/v1/batch/jobs/${JOB_ID}" \
      -H "Authorization: Bearer ${API_KEY}")

    echo "$GET_RESPONSE" | jq .
    echo "$GET_RESPONSE" | jq -R -s '{statusCode: 200, headers: {"Content-Type": "application/json"}, data: ., context: []}' > tests/Fixtures/Saloon/batch/get.json
    sed -i '' 's/\\n//g' tests/Fixtures/Saloon/batch/get.json
    echo "✅ Saved: tests/Fixtures/Saloon/batch/get.json"
    echo ""

    # List batch jobs
    echo "=== Listing Batch Jobs ==="
    LIST_RESPONSE=$(curl -s -X GET "https://api.mistral.ai/v1/batch/jobs" \
      -H "Authorization: Bearer ${API_KEY}")

    echo "$LIST_RESPONSE" | jq .
    echo "$LIST_RESPONSE" | jq -R -s '{statusCode: 200, headers: {"Content-Type": "application/json"}, data: ., context: []}' > tests/Fixtures/Saloon/batch/list.json
    sed -i '' 's/\\n//g' tests/Fixtures/Saloon/batch/list.json
    echo "✅ Saved: tests/Fixtures/Saloon/batch/list.json"
    echo ""

    # Cancel batch job
    echo "=== Canceling Batch Job ==="
    sleep 2
    CANCEL_RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/batch/jobs/${JOB_ID}/cancel" \
      -H "Authorization: Bearer ${API_KEY}")

    echo "$CANCEL_RESPONSE" | jq .
    echo "$CANCEL_RESPONSE" | jq -R -s '{statusCode: 200, headers: {"Content-Type": "application/json"}, data: ., context: []}' > tests/Fixtures/Saloon/batch/cancel.json
    sed -i '' 's/\\n//g' tests/Fixtures/Saloon/batch/cancel.json
    echo "✅ Saved: tests/Fixtures/Saloon/batch/cancel.json"

    echo ""
    echo "✅ All batch fixtures generated successfully!"
else
    echo "❌ Failed to create batch job"
    exit 1
fi
