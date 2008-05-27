###
# Authentication library for managing user cookies.
# Copyright (C) 2008 Anurag <anurag@xinh.org>

require 'cgi'
require 'openssl'
require 'base64'
require 'digest/sha1'

COOKIE_NAME = "xinh-ilug"

class PRNG
  protected
  def self.get_random_key()
    random_data = OpenSSL::BN.rand(2048, -1, false).to_s
    return OpenSSL::Digest::SHA512.new(random_data).hexdigest
  end
end


module AuthSystem
  protected
  def login_required
    logged_in?
  end
  
  def logged_in?
    current_user != nil
  end

  # Functions to base64 encode/decode cookie data
  def string_encode(data)
    return CGI.escape(Base64.encode64(data))
  end
  def string_decode(data)
    return Base64.decode64(CGI.unescape(data))
  end

  # Functions to encrypt/decrypt data
  def openssl_encrypt(data, key=RANDOM_KEY)
    return openssl_aes(:encrypt, data, key)
  end
  def openssl_decrypt(data, key=RANDOM_KEY)
    return openssl_aes(:decrypt, data, key)
  end

  private
  def openssl_aes(method, data, key)
    (cipher = OpenSSL::Cipher::Cipher.new('aes-256-cbc').send(method)).key = key
    return cipher.update(data) << cipher.final
  end
end
