#!/usr/bin/env bash

set -euo pipefail

NAMESPACE="trofimov20260527"

usage() {
    echo "Usage: $0 -t <tag>" >&2
}

TAG=""

while getopts ":t:h" opt; do
    case "$opt" in
        t)
            TAG="$OPTARG"
            ;;
        h)
            usage
            exit 0
            ;;
        :)
            echo "Option -$OPTARG requires an argument." >&2
            usage
            exit 1
            ;;
        \?)
            echo "Unknown option: -$OPTARG" >&2
            usage
            exit 1
            ;;
    esac
done

if [ -z "$TAG" ]; then
    echo "Image tag is required." >&2
    usage
    exit 1
fi

command -v kubectl >/dev/null 2>&1 || {
    echo "kubectl is required." >&2
    exit 1
}

if ! kubectl cluster-info >/dev/null 2>&1; then
    echo "Kubernetes cluster is not available." >&2
    echo "If you use Minikube, start it first:" >&2
    echo "  minikube start --driver=docker --cpus=4 --memory=4096" >&2
    exit 1
fi

IMAGE="recipehub-app:${TAG}"

echo "Applying Kubernetes namespace..."
kubectl apply -f k8s/namespace.yaml

echo "Applying Kubernetes manifests..."
kubectl apply -f k8s/

echo "Using image ${IMAGE} for recipehub-app..."
kubectl -n "$NAMESPACE" set image deployment/recipehub-app app="$IMAGE"

echo "Waiting for PostgreSQL..."
kubectl -n "$NAMESPACE" rollout status statefulset/postgres --timeout=180s

echo "Waiting for Loki..."
kubectl -n "$NAMESPACE" rollout status deployment/loki --timeout=180s

echo "Waiting for Grafana..."
kubectl -n "$NAMESPACE" rollout status deployment/grafana --timeout=180s

echo "Waiting for RecipeHub app..."
kubectl -n "$NAMESPACE" rollout status deployment/recipehub-app --timeout=240s

echo "Kubernetes deployment is ready in namespace ${NAMESPACE}."
echo "Application: kubectl -n ${NAMESPACE} port-forward svc/recipehub-app 8080:8080"
echo "Grafana:     kubectl -n ${NAMESPACE} port-forward svc/grafana 3000:3000"
