import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpServer;
import java.io.IOException;
import java.net.InetSocketAddress;
import java.nio.charset.StandardCharsets;

public class Main {
    public static void main(String[] args) throws IOException {
        HttpServer server = HttpServer.create(new InetSocketAddress(8081), 0);
        server.createContext("/health", Main::health);
        server.createContext("/", Main::root);
        server.start();
    }

    private static void health(HttpExchange exchange) throws IOException {
        respond(exchange, 200, "{\"status\":\"healthy\",\"service\":\"java\"}");
    }

    private static void root(HttpExchange exchange) throws IOException {
        respond(exchange, 200, "{\"message\":\"Java integration online\"}");
    }

    private static void respond(HttpExchange exchange, int status, String body) throws IOException {
        byte[] response = body.getBytes(StandardCharsets.UTF_8);
        exchange.getResponseHeaders().set("Content-Type", "application/json");
        exchange.sendResponseHeaders(status, response.length);
        exchange.getResponseBody().write(response);
        exchange.close();
    }
}
