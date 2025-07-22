#!/bin/bash

API_URL="http://localhost:8000"

echo "🧪 Probando API REST..."

# Test 1: Login
echo "1️⃣ Probando login..."
TOKEN_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}')

echo "Respuesta login: $TOKEN_RESPONSE"

# Extraer token (simple parsing)
TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Error: No se pudo obtener el token"
    exit 1
fi

echo "✅ Token obtenido: ${TOKEN:0:20}..."

# Test 2: Obtener proyectos
echo ""
echo "2️⃣ Obteniendo proyectos..."
curl -s -X GET "$API_URL/projects" \
  -H "Authorization: Bearer $TOKEN" | jq '.' || echo "Proyectos obtenidos (sin jq)"

# Test 3: Obtener proyecto por slug
echo ""
echo "3️⃣ Obteniendo proyecto por slug..."
curl -s -X GET "$API_URL/projects/sistema-web" \
  -H "Authorization: Bearer $TOKEN" | jq '.' || echo "Proyecto obtenido (sin jq)"

# Test 4: Obtener tareas de un proyecto
echo ""
echo "4️⃣ Obteniendo tareas del proyecto..."
curl -s -X GET "$API_URL/projects/550e8400-e29b-41d4-a716-446655440001/tasks" \
  -H "Authorization: Bearer $TOKEN" | jq '.' || echo "Tareas obtenidas (sin jq)"

# Test 5: Crear nueva tarea
echo ""
echo "5️⃣ Creando nueva tarea..."
curl -s -X POST "$API_URL/tasks" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Tarea de prueba desde script",
    "description": "Esta tarea fue creada para probar la API",
    "priority": "medium",
    "dueDate": "2025-08-15",
    "projectId": "550e8400-e29b-41d4-a716-446655440001"
  }' | jq '.' || echo "Tarea creada (sin jq)"

echo ""
echo "🎉 Pruebas completadas!"
