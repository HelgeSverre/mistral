#!/bin/bash

API_KEY=$(grep MISTRAL_API_KEY .env | cut -d '=' -f2 | tr -d '"')
FIXTURE_DIR="tests/Fixtures/Saloon"

echo "=== Generating SimpleChat Fixtures ==="

# 1. Basic chat
echo -e "\n1. Basic chat completion..."
RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/chat/completions" \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "open-mistral-7b",
    "messages": [{"role": "user", "content": "Say the word \"banana\" in your response."}]
  }')

jq -n \
  --argjson data "$RESPONSE" \
  '{statusCode: 200, headers: {}, data: ($data | tostring), context: []}' \
  > "${FIXTURE_DIR}/simpleChat.createChatCompletion.json"
echo "✅ Saved: simpleChat.createChatCompletion.json"

# 2. JSON mode
echo -e "\n2. JSON mode chat completion..."
RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/chat/completions" \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "mistral-small-latest",
    "messages": [{"role": "user", "content": "Extract the information from this text: \"John Doe is 30 years old. His email is john.doe@example.com\" Return as JSON with keys: name, age, email"}],
    "response_format": {"type": "json_object"}
  }')

jq -n \
  --argjson data "$RESPONSE" \
  '{statusCode: 200, headers: {}, data: ($data | tostring), context: []}' \
  > "${FIXTURE_DIR}/simpleChat.createChatCompletion-jsonMode.json"
echo "✅ Saved: simpleChat.createChatCompletion-jsonMode.json"

# 3. Streaming chat
echo -e "\n3. Streaming chat completion..."
RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/chat/completions" \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "open-mistral-7b",
    "messages": [{"role": "user", "content": "Say hello in 3 words"}],
    "stream": true
  }')

jq -n \
  --arg data "$RESPONSE" \
  '{statusCode: 200, headers: {"Content-Type": "text/event-stream; charset=utf-8"}, data: $data, context: []}' \
  > "${FIXTURE_DIR}/simpleChat.createStreamedChatCompletion.json"
echo "✅ Saved: simpleChat.createStreamedChatCompletion.json"

# 4. Streaming JSON mode
echo -e "\n4. Streaming JSON mode chat completion..."
RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/chat/completions" \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "mistral-small-latest",
    "messages": [{"role": "user", "content": "Extract the information from this text: \"John Doe is 30 years old. His email is john.doe@example.com\" Return as JSON with keys: name, age, email"}],
    "response_format": {"type": "json_object"},
    "stream": true
  }')

jq -n \
  --arg data "$RESPONSE" \
  '{statusCode: 200, headers: {"Content-Type": "text/event-stream; charset=utf-8"}, data: $data, context: []}' \
  > "${FIXTURE_DIR}/simpleChat.createStreamedChatCompletion-jsonMode.json"
echo "✅ Saved: simpleChat.createStreamedChatCompletion-jsonMode.json"

echo -e "\n✅ All SimpleChat fixtures generated!"
