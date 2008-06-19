#  Copyright (c) 2008, Anurag Patel <anurag@xinh.org>
#  All rights reserved.
#
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions are met:
#      * Redistributions of source code must retain the above copyright
#        notice, this list of conditions and the following disclaimer.
#      * Redistributions in binary form must reproduce the above copyright
#        notice, this list of conditions and the following disclaimer in the
#        documentation and/or other materials provided with the distribution.
#      * Neither the name of the Xinh Associates nor the
#        names of its contributors may be used to endorse or promote products
#        derived from this software without specific prior written permission.
#
#  THIS SOFTWARE IS PROVIDED BY ANURAG PATEL ``AS IS'' AND ANY
#  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
#  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
#  DISCLAIMED. IN NO EVENT SHALL ANURAG PATEL BE LIABLE FOR ANY
#  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
#  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
#  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
#  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
#  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
#  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

###
# Authentication library for managing user cookies.
# Copyright (C) 2008 Anurag <anurag@xinh.org>

require 'cgi'
require 'openssl'
require 'base64'
require 'digest/sha1'

class PRNG
  protected
  def self.get_random_key()
    puts "Generating randomness"
    random_data = OpenSSL::BN.rand(4096, -1, false).to_s
    return OpenSSL::Digest::SHA512.new(random_data).hexdigest
  end
end


module AuthSystem
  protected

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

  # Function to set login cookie if the user is authenticated.
  def set_login_cookie(user_data)
    user_data_encrypted = string_encode(openssl_encrypt(user_data))
    user_data_checksum  = OpenSSL::Digest::SHA1.new(user_data).hexdigest
    cookies[COOKIE_NAME] = user_data_encrypted + ":" + user_data_checksum
  end
  
  # Function to retrieve user's PSV, after decrypting the user cookie.
  def get_user_from_login_cookie
    user_cookie = cookies[COOKIE_NAME]
    if not user_cookie.nil?
      begin
        user_data_encrypted, user_data_checksum = user_cookie.split(":")
        if not user_data_encrypted.nil? and not user_data_checksum.nil?
          user_data = openssl_decrypt(string_decode(user_data_encrypted))
          return user_data if (OpenSSL::Digest::SHA1.new(user_data).hexdigest == user_data_checksum)
        end
      rescue
        cookies.delete COOKIE_NAME
      end      
    end
    return nil
  end

  private
  def openssl_aes(method, data, key)
    (cipher = OpenSSL::Cipher::Cipher.new('aes-256-cbc').send(method)).key = key
    return cipher.update(data) << cipher.final
  end
end
