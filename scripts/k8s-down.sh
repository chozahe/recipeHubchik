#!/usr/bin/env bash

set -euo pipefail

NAMESPACE="trofimov20260527"

echo "Deleting Kubernetes namespace ${NAMESPACE}..."
kubectl delete namespace "$NAMESPACE" --ignore-not-found
