#!/bin/bash

API_KEY=$(grep MISTRAL_API_KEY .env | cut -d '=' -f2 | tr -d '"')
FIXTURE_DIR="tests/Fixtures/Saloon"

echo "=== Generating Chat Fixtures ==="

# 1. Basic chat completion
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
  > "${FIXTURE_DIR}/chat.createChatCompletion.json"
echo "✅ Saved: chat.createChatCompletion.json"

# 2. JSON mode chat completion
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
  > "${FIXTURE_DIR}/chat.createChatCompletion-jsonMode.json"
echo "✅ Saved: chat.createChatCompletion-jsonMode.json"

# 3. Function calling
echo -e "\n3. Function calling chat completion..."
RESPONSE=$(curl -s -X POST "https://api.mistral.ai/v1/chat/completions" \
  -H "Authorization: Bearer ${API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "mistral-small-latest",
    "messages": [{"role": "user", "content": "What is the weather in Paris?"}],
    "tools": [
      {
        "type": "function",
        "function": {
          "name": "searchWeather",
          "description": "Get the weather for a location",
          "parameters": {
            "type": "object",
            "required": ["location"],
            "properties": {
              "location": {
                "type": "string",
                "description": "The location to get the weather for."
              }
            }
          }
        }
      }
    ],
    "tool_choice": "any"
  }')

jq -n \
  --argjson data "$RESPONSE" \
  '{statusCode: 200, headers: {}, data: ($data | tostring), context: []}' \
  > "${FIXTURE_DIR}/chat.createChatCompletion-functionCall.json"
echo "✅ Saved: chat.createChatCompletion-functionCall.json"

echo -e "\n✅ All Chat fixtures generated!"
