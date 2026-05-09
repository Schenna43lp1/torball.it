FROM golang:1.23-alpine AS builder

WORKDIR /app

COPY go.mod .
RUN go mod download

COPY . .

RUN go build -o torball-api .

FROM alpine:latest

WORKDIR /root/

COPY --from=builder /app/torball-api .

EXPOSE 8082

CMD ["./torball-api"]
